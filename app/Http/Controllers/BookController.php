<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
   /**
     * Display a listing of the books.
     */
    public function index()
    {
        // Retrieve all books
        $books = Book::all();
        //return the books as a JSON response
        return response()->json($books);
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        // Retrieve a single book by its ID
        $book = Book::find($id);

        // Check if the book exists
        if (!$book) {
            //Return 404 if the book not found 
            return response()->json(['message' => 'Book not found']) ;
        }
        //Return the book as a JSON response
        return response()->json($book);
    }
    
    /**
     * Create a new book using request data
     */ 

    public function create(Request $request)
    {
        // Retrieve individual properties from the request
        $book = Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'description' => $request->description,
            'year_published' => $request->year_published,
        ]);

        // Return a response
        return response()->json([
            'message' => 'Book created successfully!',
            'book' => $book,
        ], 404);
    }

    public function update(Request $request, $id)
    {
        // Find the book by its ID
        $book = Book::find($id);

        // Check if the book exists
        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
            ], 404);
        }

        // Update the book properties individually from the request
        $book->title = $request->title;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->year_published = $request->year_published;

        // Save the updated book
        $book->save();
        // Return a response
        return response()->json([
            'message' => 'Book updated successfully!',
            'book' => $book,
        ], 200);
    }

    /**
     * Remove the specified book from storage.
     */

    public function delete($id)
    {
        // Find the book by its ID
        $book = Book::find($id);

        // Check if the book exists
        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
            ], 404);
        }

        // Delete the book
        $book->delete();

        // Return a success response
        return response()->json(['message' => 'Book deleted successfully!',], 200);
    }

}
