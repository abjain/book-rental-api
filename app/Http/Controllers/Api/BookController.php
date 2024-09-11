<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query();

        $params =  $request->query();

        if (array_key_exists('title',$params) && $params['title'] != "") {
            $query->where('title', 'like', '%' . trim($params['title']) . '%');
        }

        if (array_key_exists('genre',$params) && $params['genre'] != "") {
            $query->where('genre', 'like', '%' . trim($params['genre']) . '%');
        }

        $books = $query->get();

        return response()->json([
            'success' => true,
            'books' => $books
        ], 200);
    }

    public function show($id)
    {
        $user = Auth::user();
        $book = Book::findOrFail($id);
        $bookRented = Rental::where('book_id', $book->id)->where('user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'rentals' => $bookRented
        ], 200);
    }

    public function rent($id)
    {
        $user = Auth::user();
        $book = Book::findOrFail($id);

        $rental = Rental::firstOrCreate([
            'user_id' => $user->id,
            'book_id' => $book->id
        ]);

        $rental->status = 'rent';
        $rental->rental_date = now();
        $rental->return_date = now()->addWeeks(2);
        $rental->save();

        return response()->json([
            'success' => true,
            'rentals' => 'Book rented successfully.'
        ], 200);
    }

    public function return($id)
    {
        $user = Auth::user();
        $book = Book::findOrFail($id);
        $deleteRental = Rental::where('book_id', $book->id)->where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'rentals' => 'Book returned successfully.'
        ], 200);
    }

    public function rentals() {
        $books = Book::with('rentals')->join('rentals', function ($join){
            $join->on('rentals.book_id', '=', 'books.id');
        })  ->select(DB::raw('books.*'))
            ->groupBy('books.id')
            ->get();

        return response()->json([
            'success' => true,
            'books' => $books
        ], 200);
    }

    public function stats() {
        $data = DB::table('books')
            ->leftJoin('rentals','books.id','=','rentals.book_id')
            ->selectRaw('books.*, count(rentals.book_id) total_purchase,MAX(DATEDIFF(NOW(), rentals.return_date)) AS overdue_days')
            ->groupBy('books.id')
            ->orderBy('total_purchase','desc')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $data,
        ], 200);
    }
}
