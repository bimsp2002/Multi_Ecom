<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category; 
use App\Models\Brand;
use App\Models\Product;
use App\Models\User; 


class ShopController extends Controller
{


  public function ShopPage(Request $request)
  {
      // Start with a base query that only fetches active products
      $products = Product::where('status', 1);
  
      // Category filter
      if (!empty($request->category)) {
          $slugs = explode(',', $request->category);
          $catIds = Category::whereIn('category_slug', $slugs)->pluck('id')->toArray();
          $products = $products->whereIn('category_id', $catIds);
      }
  
      // Brand filter
      if (!empty($request->brand)) {
          $slugs = explode(',', $request->brand);
          $brandIds = Brand::whereIn('brand_slug', $slugs)->pluck('id')->toArray();
          $products = $products->whereIn('brand_id', $brandIds);
      }
  
      // Price range filter
      if (!empty($request->price)) {
          [$minPrice, $maxPrice] = explode('-', $request->price);
          $products = $products->whereBetween('selling_price', [(int)$minPrice, (int)$maxPrice]);
      }
  
      // Get the filtered products with pagination
      $products = $products->orderBy('id', 'DESC')->paginate(12);
  
      // Fetch categories, brands, and new products for sidebar
      $categories = Category::orderBy('category_name', 'ASC')->get();
      $brands = Brand::orderBy('brand_name', 'ASC')->get();
      $newProduct = Product::orderBy('id', 'DESC')->limit(3)->get();
  
      return view('frontend.product.shop_page', compact('products', 'categories', 'newProduct', 'brands'));
  }
  
 // End Method 


 public function ShopFilter(Request $request)
 {
     $data = $request->all();
 
     // Initialize the category, brand, and price range URL parts
     $catUrl = "";
     $brandUrl = "";
     $priceRangeUrl = "";
 
     // Filter for Category
     if (!empty($data['category'])) {
         foreach ($data['category'] as $category) {
             $catUrl .= (empty($catUrl) ? '&category=' : ',') . $category;
         }
     }
 
     // Filter for Brand
     if (!empty($data['brand'])) {
         foreach ($data['brand'] as $brand) {
             $brandUrl .= (empty($brandUrl) ? '&brand=' : ',') . $brand;
         }
     }
 
     // Filter for Price Range
     if (!empty($data['price_range'])) {
         [$minPrice, $maxPrice] = explode('-', $data['price_range']);
         $priceRangeUrl .= '&price=' . $data['price_range'];
     }
 
     // Redirect to the shop page route with the filters applied
     return redirect()->route('shop.page', $catUrl . $brandUrl . $priceRangeUrl);
 }
 // End Method 




}
 