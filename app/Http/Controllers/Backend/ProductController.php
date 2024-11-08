<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\MultiImg;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Image;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ProductController extends Controller
{
    public function AllProduct()
    {
        $products = Product::latest()->get();
        return view('backend.product.product_all', compact('products'));
    } // End Method 

    public function AddProduct()
    {
        $activeVendor = User::where('status', 'active')->where('role', 'vendor')->latest()->get();
        $brands = Brand::latest()->get();
        $categories = Category::latest()->get();
        return view('backend.product.product_add', compact('brands', 'categories', 'activeVendor'));
    } // End Method 



    public function StoreProduct(Request $request)
    {

        if ($request->file('product_thambnail')) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $request->file('product_thambnail')->getClientOriginalExtension();
            $img = $manager->read($request->file('product_thambnail'));
            $img = $img->resize(400, 400);
            $img->toJpeg(80)->save(base_path('public/upload/product/thambnail/' . $name_gen));
            $save_url = 'upload/product/thambnail/' . $name_gen;

            $product_id = Product::insertGetId([

                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'product_name' => $request->product_name,
                'product_slug' => strtolower(str_replace(' ', '-', $request->product_name)),

                'product_code' => $request->product_code,
                'product_qty' => $request->product_qty,
                'product_tags' => $request->product_tags,
                'product_size' => $request->product_size,
                'product_color' => $request->product_color,

                'selling_price' => $request->selling_price,
                'discount_price' => $request->discount_price,
                'short_descp' => $request->short_descp,
                'long_descp' => $request->long_descp,

                'hot_deals' => $request->hot_deals,
                'featured' => $request->featured,
                'special_offer' => $request->special_offer,
                'special_deals' => $request->special_deals,

                'product_thambnail' => $save_url,
                'vendor_id' => $request->vendor_id,
                'status' => 1,
                'created_at' => Carbon::now(),

            ]);
        }

        /// Multiple Image Upload  //////
        if ($request->hasFile('multi_img')) {
            $images = $request->file('multi_img');
            $manager = new ImageManager(new Driver());

            foreach ($images as $img) {
                $name_gen = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension(); // Use $img instead of $request->file('multi_img')
                $img = $manager->read($img); // Read the current image
                $img = $img->resize(400, 400);
                $img->toJpeg(80)->save(base_path('public/upload/product/multi_img/' . $name_gen)); // Added '/' before $name_gen
                $save_url = 'upload/product/multi_img/' . $name_gen; // Added '/' before $name_gen

                MultiImg::insert([
                    'product_id' => $product_id,
                    'photo_name' => $save_url, // Corrected the variable name (removed extra $)
                    'created_at' => Carbon::now(),
                ]);
            }
        } // end foreach

        /// End Multiple Image Upload From her //////

        $notification = array(
            'message' => 'Product Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.product')->with($notification);
    } // End Method 



    public function EditProduct($id)
    {

        $multiImgs = MultiImg::where('product_id', $id)->get();
        $activeVendor = User::where('status', 'active')->where('role', 'vendor')->latest()->get();
        $brands = Brand::latest()->get();
        $categories = Category::latest()->get();
        $subcategory = SubCategory::latest()->get();
        $products = Product::findOrFail($id);
        return view('backend.product.product_edit', compact('brands', 'categories', 'activeVendor', 'products', 'subcategory', 'multiImgs'));
    } // End Method 


    public function UpdateProduct(Request $request)
    {

        $product_id = $request->id;

        Product::findOrFail($product_id)->update([

            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'product_name' => $request->product_name,
            'product_slug' => strtolower(str_replace(' ', '-', $request->product_name)),

            'product_code' => $request->product_code,
            'product_qty' => $request->product_qty,
            'product_tags' => $request->product_tags,
            'product_size' => $request->product_size,
            'product_color' => $request->product_color,

            'selling_price' => $request->selling_price,
            'discount_price' => $request->discount_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,

            'hot_deals' => $request->hot_deals,
            'featured' => $request->featured,
            'special_offer' => $request->special_offer,
            'special_deals' => $request->special_deals,


            'vendor_id' => $request->vendor_id,
            'status' => 1,
            'created_at' => Carbon::now(),

        ]);
        $notification = array(
            'message' => 'Product Updated Without Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.product')->with($notification);
    } // End Method

    public function UpdateProductThambnail(Request $request)
    {

        $pro_id = $request->id;
        $oldImage = $request->old_img;
        if ($request->file('product_thambnail')) {

            $image = $request->file('product_thambnail');
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $name_gen = hexdec(uniqid()) . '.' . $request->file('product_thambnail')->getClientOriginalExtension();
            $img = $manager->read($request->file('product_thambnail'));
            $img = $img->resize(400, 400);
            $img->toJpeg(80)->save(base_path('public/upload/product/thambnail/' . $name_gen));
            $save_url = 'upload/product/thambnail/' . $name_gen;

            if (file_exists($oldImage)) {
                unlink($oldImage);
            }

            Product::findOrFail($pro_id)->update([

                'product_thambnail' => $save_url,
                'updated_at' => Carbon::now(),
            ]);

            $notification = array(
                'message' => 'Product Image Thambnail Updated Successfully',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        }
    } // End Method
    // Multi Image Update 
    public function UpdateProductMultiimage(Request $request)
    {

        if ($request->hasFile('multi_img')) {
            $images = $request->file('multi_img');
            $manager = new ImageManager(new Driver());
            foreach ($images as $id => $img) {
                $imgDel = MultiImg::findOrFail($id);
                unlink($imgDel->photo_name);
                $name_gen = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension(); // Use $img instead of $request->file('multi_img')
                $img = $manager->read($img); // Read the current image
                $img = $img->resize(400, 400);
                $img->toJpeg(80)->save(base_path('public/upload/product/multi_img/' . $name_gen)); // Added '/' before $name_gen
                $save_url = 'upload/product/multi_img/' . $name_gen; // Added '/' before $name_gen

                MultiImg::where('id', $id)->update([
                    'photo_name' => $save_url,
                    'updated_at' => Carbon::now(),

                ]);
            }
        } // end foreach

        $notification = array(
            'message' => 'Product Multi Image Updated Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 





    public function MulitImageDelelte($id)
    {
        $oldImg = MultiImg::findOrFail($id);
        unlink($oldImg->photo_name);

        MultiImg::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Product Multi Image Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    } // End Method 


    public function ProductInactive($id){

        Product::findOrFail($id)->update(['status' => 0]);
        $notification = array(
            'message' => 'Product Inactive',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method 

    public function ProductActive($id){

        Product::findOrFail($id)->update(['status' => 1]);
        $notification = array(
            'message' => 'Product Active',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method 

    public function ProductDelete($id){

        $product = Product::findOrFail($id);
        unlink($product->product_thambnail);
        Product::findOrFail($id)->delete();

        $imges = MultiImg::where('product_id',$id)->get();
        foreach($imges as $img){
            unlink($img->photo_name);
            MultiImg::where('product_id',$id)->delete();
        }

        $notification = array(
            'message' => 'Product Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }// End Method 

    public function ProductStock(){

        $products = Product::latest()->get();
        return view('backend.product.product_stock',compact('products'));

    }// End Method 



}
