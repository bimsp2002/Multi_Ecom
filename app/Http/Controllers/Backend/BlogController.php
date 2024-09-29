<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\BlogPost;

use App\Models\SubCategory;
use App\Models\MultiImg;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Image;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BlogController extends Controller
{
    public function AllBlogCateogry()
    {

        $blogcategoryies = BlogCategory::latest()->get();
        return view('backend.blog.category.blogcategroy_all', compact('blogcategoryies'));
    } // End Method 

    public function AddBlogCateogry()
    {
        return view('backend.blog.category.blogcategroy_add');
    } // End Method 

    public function StoreBlogCateogry(Request $request)
    {

        BlogCategory::insert([
            'blog_category_name' => $request->blog_category_name,
            'blog_category_slug' => strtolower(str_replace(' ', '-', $request->blog_category_name)),
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Blog Category Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.blog.category')->with($notification);
    } // End Method 

    public function EditBlogCateogry($id)
    {

        $blogcategoryies = BlogCategory::findOrFail($id);
        return view('backend.blog.category.blogcategroy_edit', compact('blogcategoryies'));
    } // End Method 

    public function UpdateBlogCateogry(Request $request)
    {

        $blog_id = $request->id;

        BlogCategory::findOrFail($blog_id)->update([
            'blog_category_name' => $request->blog_category_name,
            'blog_category_slug' => strtolower(str_replace(' ', '-', $request->blog_category_name)),
        ]);

        $notification = array(
            'message' => 'Blog Category Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.blog.category')->with($notification);
    } // End Method 

    public function DeleteBlogCateogry($id)
    {
        BlogCategory::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Blog Category Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 

    //////////////////// Blog Post Methods //////////////////


    public function AllBlogPost()
    {

        $blogpost = BlogPost::latest()->get();
        return view('backend.blog.post.blogpost_all', compact('blogpost'));
    } // End Method 

    public function AddBlogPost()
    {
        $blogcategory = BlogCategory::latest()->get();
        return view('backend.blog.post.blogpost_add', compact('blogcategory'));
    } // End Method 

    public function StoreBlogPost(Request $request)
    {

        if ($request->file('post_image')) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $request->file('post_image')->getClientOriginalExtension();
            $img = $manager->read($request->file('post_image'));
            $img = $img->resize(1103, 906);
            $img->toJpeg(80)->save(base_path('public/upload/product/blog/' . $name_gen));
            $save_url = 'upload/product/blog/' . $name_gen;

            BlogPost::insert([
                'category_id' => $request->category_id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
                'post_short_description' => $request->post_short_description,
                'post_long_description' => $request->post_long_description,
                'post_image' => $save_url,
                'created_at' => Carbon::now(),

            ]);
        }

        $notification = array(
            'message' => 'Blog Post Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('admin.blog.post')->with($notification);
    } // End Method 


    public function EditBlogPost($id)
    {
        $blogcategory = BlogCategory::latest()->get();
        $blogpost = BlogPost::findOrFail($id);
        return view('backend.blog.post.blogpost_edit', compact('blogcategory', 'blogpost'));
    } // End Method 

    public function UpdateBlogPost(Request $request)
    {


        $post_id = $request->id;
        $oldImage = $request->old_img;
        if ($request->file('post_image')) {

            $image = $request->file('post_image');
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $name_gen = hexdec(uniqid()) . '.' . $request->file('post_image')->getClientOriginalExtension();
            $img = $manager->read($request->file('post_image'));
            $img = $img->resize(400, 400);
            $img->toJpeg(80)->save(base_path('public/upload/product/blog/' . $name_gen));
            $save_url = 'upload/product/blog/' . $name_gen;

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }

            BlogPost::findOrFail($post_id)->update([
                'category_id' => $request->category_id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
                'post_short_description' => $request->post_short_description,
                'post_long_description' => $request->post_long_description,
                'post_image' => $save_url,
                'updated_at' => Carbon::now(),
            ]);

            $notification = array(
                'message' => 'Blog Post Updated with image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('admin.blog.post')->with($notification);
        } else {

            BlogPost::findOrFail($post_id)->update([
                'category_id' => $request->category_id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
                'post_short_description' => $request->post_short_description,
                'post_long_description' => $request->post_long_description,
                'updated_at' => Carbon::now(),
            ]);

            $notification = array(
                'message' => 'Blog Post Updated without image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('admin.blog.post')->with($notification);
        } // end else

    } // End Method 

    public function DeleteBlogPost($id){

        $blogpost = BlogPost::findOrFail($id);
        $img = $blogpost->post_image;
        unlink($img ); 

        BlogPost::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Blog Post Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 

    }// End Method 

     //////////////////// Frontend Blog All Method //////////////


     public function AllBlog(){
        $blogcategoryies = BlogCategory::latest()->get();
        $blogpost = BlogPost::latest()->get();
        return view('frontend.blog.home_blog',compact('blogcategoryies','blogpost'));
    }// End Method 




}
