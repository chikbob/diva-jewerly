<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Product::query()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id'])
        ]);
    }

    public function home(): \Inertia\Response
    {
        // Получаем все категории с изображениями
        $categories = Category::all();

        // Отправляем категории на главную страницу
        return Inertia::render('Products/Home', [
            'categories' => $categories
        ]);
    }

}
