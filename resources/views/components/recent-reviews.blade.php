{{-- resources/views/components/recent-reviews.blade.php --}}

<div class="col-lg-12">
    <div class="card stretch stretch-full">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Recent Customer Reviews</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="reviewList">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Book</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReviews as $review)
                        <tr>
                            {{-- Customer --}}
                            <td>
                                <div class="hstack gap-3">
                                    <div class="avatar-image avatar-md">
                                        <img src="{{ empty($review->user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $review->user->profile_photo) }}"
                                             alt="user" class="img-fluid rounded-circle">
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-truncate-1-line">{{ $review->user->name }}</span>
                                        <small class="text-muted d-block">{{ $review->user->email }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Book --}}
                            <td>
                                <a href="{{ route('admin.books.show', $review->book->id) }}"
                                   class="fw-semibold text-truncate-1-line text-decoration-none">
                                    {{ $review->book->title }}
                                </a>
                                <small class="text-muted d-block">{{ $review->book->category->name ?? '—' }}</small>
                            </td>

                            {{-- Star Rating --}}
                            <td>
                                <div class="hstack gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="feather-star fs-11"
                                           style="color: {{ $i <= $review->rating ? '#f59e0b' : '#e2e8f0' }}; fill: {{ $i <= $review->rating ? '#f59e0b' : 'none' }};"></i>
                                    @endfor
                                    <span class="ms-1 fs-12 fw-semibold text-muted">{{ $review->rating }}/5</span>
                                </div>
                            </td>

                            {{-- Comment --}}
                            <td style="max-width: 260px;">
                                @if($review->comment)
                                    <span class="text-truncate-1-line d-block" title="{{ $review->comment }}">
                                        {{ $review->comment }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">No comment</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $review->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $review->created_at->format('h:i A') }}</small>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('admin.books.show', $review->book->id) }}"
                                       class="avatar-text avatar-md"
                                       data-bs-toggle="tooltip"
                                       title="View Book">
                                        <i class="feather feather-eye"></i>
                                    </a>
                                    <form action="{{ route('reviews.destroy', $review->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="avatar-text avatar-md border-0 bg-transparent text-danger"
                                                data-bs-toggle="tooltip"
                                                title="Delete Review"
                                                style="cursor: pointer;">
                                            <i class="feather feather-trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="feather-message-square fs-2 d-block mb-2"></i>
                                No reviews yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>