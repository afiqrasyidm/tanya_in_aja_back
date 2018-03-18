<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Comment;
use App\Post_like;

use Auth;
use App;

class PostController extends Controller
{
	
    public function index()
    {

			
        return Post::all();
    }

    public function show($id)
    {
		
		
		$post = DB::table('posts')
		
			->select('posts.id as id','users.id as user_id','users.name', 'posts.created_at as created_at',
			 'posts.body as body', 'posts.title as title', 'posts.up_vote as up_vote',
			 'posts.down_vote as down_vote'
			 )
            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
			->where('posts.id', '=', $id)
            ->first();
		
		$comments = DB::table('comments')
			->select('comments.id as id','users.id as user_id', 'comments.body as body',  'comments.is_anonim', 
				'users.name as name', 'comments.created_at as created_at'
			 )
		
            ->leftJoin('users', 'users.id', '=', 'comments.user_id')
			->orderBy('comments.created_at','DESC')
			 ->where('comments.post_id', '=', $id)
            ->get();
			
		$post_likes_count = DB::table('post_likes')
			 ->where('post_likes.post_id', '=', $id)
			 ->where('flag_vote', '=',1)
			 ->count();
			 

		
		$post_likes_flag_user = null;
		if(Auth::check()){
				//ambil apakah user tersebut sudah likes apa belum kalau hasil 1 likes, 2 unlikes
				$post_likes_flag_user = DB::table('post_likes')
				->where('post_likes.post_id', '=', $id)
				->where('user_id', '=', Auth::user()->id)
				->first();
				
			
			

		}

//		return $post_likes_flag_user;
		
		if($post == NULL){
			abort(404);
		}
	//return $comments ;
		
		//dd($post);
    
        return view('belimbing/single-post')->with('post',$post)->with('comments',$comments)
		->with('post_likes_count',$post_likes_count)
		->with('post_likes_flag_user',$post_likes_flag_user);
  
	}
	
	 public function comment_post($id)
    {	
		
		//untuk submit comment
		if(Input::get('comment')){
			
			
			$comment = new Comment;
			
			if(!Auth::check()){
				//hardcode anonim user
				$comment->user_id = 27;
				$comment->is_anonim = 0;
			}

			else{
				//kalau udah login di cek apakah dia milih anonim apa nama dia sendiri			
				
				$comment->user_id = Auth::user()->id;
				$comment->is_anonim =   Input::get('is_anonim');
				
			}
			$comment->body = Input::get('body');
			$comment->post_id = $id;
			$comment->up_vote = 0;
			$comment->down_vote = 0;
			
			$comment->save();
			
			return redirect()->route('show.single.post', ['id' => $id]);
		}
		//hapus post
		else if(Input::get('hapus')){
			$post = Post::find($id);
			$post->delete();
			
			return redirect()->route('home');
		}
		//edit post
		//masuk ke text editor
		else if(Input::get('ubah')){
			
			
			
			$post = DB::table('posts')
			 ->select('posts.id as id','users.name', 'posts.created_at as created_at',
			 'posts.body as body', 'posts.title as title', 'posts.up_vote as up_vote',
			 'posts.down_vote as down_vote'
			 )
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')
			 ->where('users.id', '=', Auth::user()->id)
			 ->where('posts.id', '=', $id)
			  ->first();
			
			
			
			
			return view('belimbing/ubah-post')->with('post',$post);
		
		}
		//untuk final setelah ubah post dari text editor
		else if(Input::get('ubah_final')){
			
			
			
			$post = Post::find($id);
			$post->title = Input::get('title');

			$post->body = Input::get('body');

			$post->save();
			
			
				//return "lol";
			
			
			
			return redirect()->route('show.single.post', ['id' => $id]);
		}
		
		//hapus comment
		else if(Input::get('hapus_comment')){
			
			//return Input::get('hapus_comment');
			$comment = Comment::find(Input::get('hapus_comment'));
			$comment->delete();
			
			return redirect()->route('show.single.post', ['id' => $id]);
		}
		
    }
	
	
	
	
	
	
	public function ask()
    {
	
	


        return view('belimbing/ask-post');
    }
	
	public function ask_post()
    {
	
			$post = new Post;
			$post->title = Input::get('title');
			$post->user_id = Auth::user()->id;
			$post->post_category = 0;
			
		if(Input::get('body')!=NULL)	
			$post->body = Input::get('body');
		else
			$post->body =" ";
		
			$post->up_vote = 0;
			$post->down_vote = 0;
		
		
			$post->save();
		
			
       	
				return redirect()->route('show.single.post', ['id' => $post->id]);
		
	
    }
	
	
	
    public function show_post_uid()
	
	{

	
		
		
	 
		$posts = DB::table('posts')
			 ->select('posts.id as id','users.name', 'posts.created_at as created_at',
			 'posts.body as body', 'posts.title as title', 'posts.up_vote as up_vote',
			 'posts.down_vote as down_vote'
			 )
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')
			 ->where('users.id', '=', Auth::user()->id)
			 ->orderBy('posts.created_at','DESC')->paginate(10);
			 

		//return "asda";
	
		

		//dd($posts);

		return view('belimbing/myquestion')->with('posts',$posts);

//		return response()->json($posts, 201);
    }
	

    public function store(Request $request)
    {
		$user_id = Auth::guard('api')->id();
		
		
        $post = Post::create($request->all()+ ['user_id' => $user_id ]);
		
        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post)
    {
	
		$user_id = Auth::guard('api')->id();
		
        $post->update($request->all()) + ['user_id' => $user_id ];

        return response()->json($post, 200);
    }

    public function delete( $post_id)
    {
		
			
		DB::table('posts')->where('id', '=', $post_id)->delete();

        return response()->json(null, 204);
    }
	public function up_vote(Request $request)
    {
		
	
	  if($request->action == 1){
		$post_like = new Post_like;
		$post_like->user_id =Auth::user()->id;
		$post_like->post_id = $request->post_id;
		$post_like->flag_vote = 1;
		$post_like->save();
	  }
	  else{
		  $post_like = Post_like::where('user_id', Auth::user()->id)
						->where('post_id', $request->post_id)
						->delete();
	  }
		
		
		$post_likes_count = DB::table('post_likes')
			 ->where('post_likes.post_id', '=', $request->post_id)
			 ->where('flag_vote', '=',1)
			 ->count();
			 
			 
        $response = array(
			'status' => "saved" ,
			'msg'    => 'Setting created successfully',
			'post_likes_count' =>$post_likes_count,
		);

		return  \Response::json($response);
    }
	
	
	
	
	
	
}
