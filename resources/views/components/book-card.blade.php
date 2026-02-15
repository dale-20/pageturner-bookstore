@props(['book'])

<div class="col-md-3">
    <div class="product-item">
        <figure class="product-style">
            <img src="{{ empty($book->cover_image) ? asset('booksaw/images/main-banner1.jpg') : asset($book->cover_image) }}"
                alt="{{ $book->title }}" class="product-item">
            <a href="{{ route('books.show', $book) }}">
                <button type="button" class="add-to-cart" data-product-tile="add-to-cart">View Details</button>
            </a>
        </figure>
        <figcaption>
            <h3>{{ $book->title }}</h3>
            <span>{{ $book->author }}</span>
            <div class="item-price">${{ $book->price }}</div>

            <!-- Small stars with inline styles -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 2px; margin-bottom: 8px;">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($book->average_rating))
                        <svg style="width: 14px; height: 14px; color: #fbbf24;" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @else
                        <svg style="width: 14px; height: 14px; color: #d1d5db;" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endif
                @endfor
                <span style="margin-left: 4px; font-size: 11px; color: #6b7280;">({{ $book->reviews->count() }})</span>
            </div>

        </figcaption>
    </div>
</div>