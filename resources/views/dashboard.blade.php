@extends('layouts.app')

@section('title', 'PageTurner - Online Bookstore')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <hr>

                <div class="section-header align-center">
                    <div class="title">
                        <span>Discover your next favorite book from our extensive collection</span>
                    </div>
                    <h2 class="section-title">Featured Books</h2>
                </div>

                @php
                    $current_category = request()->input('category', session('current_category', 'all_genre'));

                    session(['current_category' => $current_category]);
                @endphp

                <ul class="tabs">
                    <li onclick="window.location.href='?category=all_genre'"
                        class="tab {{ $current_category == 'all_genre' ? 'active' : '' }}">All Genre</li>

                    @foreach($categories as $category)
                        <li onclick="window.location.href='?category={{ $category->name }}'"
                            class="tab {{ $current_category == $category->name ? 'active' : '' }}">
                            {{ $category->name }}
                        </li>
                    @endforeach
                </ul>


                @php
                    // Filter books based on current category
                    $filteredBooks = ($current_category == 'all_genre')
                        ? $featuredBooks
                        : $featuredBooks->filter(function ($book) use ($current_category) {
                            return $book->category->name == $current_category;
                        });
                @endphp

                <div class="tab-content">
                    @if($filteredBooks->count() > 0)
                        <div class="row">
                            @foreach ($filteredBooks as $featuredBook)
                                <x-book-card :book="$featuredBook" />
                                @if($loop->iteration % 4 == 0)
                                    </div>
                                    <div class="row">
                                @endif
                            @endforeach
                    @else
                            <div class="col-12">
                                <p class="text-center">No books found in this category.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <section id="quotation" class="align-center pb-5 mb-5">
            <div class="inner-content">
                <h2 class="section-title divider">Quote of the day</h2>
			<blockquote data-aos="fade-up">
				<q>“The more that you read, the more things you will know. The more that you learn, the more places
					you’ll go.”</q>
				<div class="author-name">Dr. Seuss</div>
			</blockquote>
            </div>
        </section>
@endsection