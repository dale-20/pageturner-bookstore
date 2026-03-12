<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewAdminNotification;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store or update a review.
     * Notifies: all admins when a NEW review is submitted (not on update).
     */
    public function store(Request $request, Book $book)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['book_id'] = $book->id;

        $existingReview = Review::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($existingReview) {
            $existingReview->update($validated);
            $message = 'Review updated successfully!';
        } else {
            $review = Review::create($validated);
            $message = 'Review submitted successfully!';

            // Notify all admins — only for brand new reviews
            $review->load(['user', 'book']);
            User::where('role', 'admin')->each(function ($admin) use ($review) {
                $admin->notify(new NewReviewAdminNotification($review));
            });
        }

        return redirect()->route('books.show', $book)->with('success', $message);
    }

    public function destroy(Review $review)
    {
        if (auth()->id() !== $review->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Review deleted successfully!');
    }
}