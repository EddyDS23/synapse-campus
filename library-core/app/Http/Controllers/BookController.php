<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{

    public function getOne(Request $request, string $id):JsonResponse{

        $book = Book::where('id',$id)->first();
        return response()->json([$book],200);
    }

    
    public function getAll(Request $request):JsonResponse{

        $perPage = 10;

        $title = $request->query('title');
        $autor = $request->query('autor');
        $category = $request->query('category');
        $available = $request->query('available');

        $query = Book::query();

        if($title !== null){
            $query->where('title',$title);
        }

        if($autor !== null){
            $query->where('autor',$autor);
        }

        if($category !== null){
            $query->where('category',$category);
        }

        if($available === true){
            $query->where('stock_available','>','0');
        }

        $query->select(['id','title','author','category']);

        $books = $query->paginate($perPage);

        return response()->json([
            'meta'=>[
                'perPage'=>$books->perPage(),
                'currentPage'=>$books->currentPage(),
                'totalPage'=>$books->lastPage(),
                'total'=>$books->total()
            ],
            'data'=>$books->items(),
        ],200);

    }

}
