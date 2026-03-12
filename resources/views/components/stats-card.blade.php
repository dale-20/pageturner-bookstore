{{-- resources/views/components/stats-card.blade.php --}}

{{-- Users --}}
<div class="col-xxl-3 col-md-6">
    <div class="card stretch stretch-full" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07);">
        <div class="card-body p-0">
            <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="feather-users" style="color: white; font-size: 1.3rem;"></i>
                    </div>
                    <a href="{{ route('admin.users', 'customer') }}"
                       style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        <i class="feather-arrow-right" style="color: white; font-size: 0.9rem;"></i>
                    </a>
                </div>
                <div style="color: white; font-size: 2rem; font-weight: 800; line-height: 1;">
                    {{ number_format($stats['totalUsers']) }}
                </div>
                <div style="color: rgba(255,255,255,0.75); font-size: 0.82rem; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.8px;">
                    Total Users
                </div>
            </div>
            <div style="padding: 0.85rem 1.5rem; background: white; display: flex; align-items: center; gap: 6px;">
                <i class="feather-users" style="color: #6366f1; font-size: 0.8rem;"></i>
                <span style="font-size: 0.78rem; color: #64748b;">Registered accounts</span>
            </div>
        </div>
    </div>
</div>

{{-- Orders --}}
<div class="col-xxl-3 col-md-6">
    <div class="card stretch stretch-full" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07);">
        <div class="card-body p-0">
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="feather-briefcase" style="color: white; font-size: 1.3rem;"></i>
                    </div>
                    <a href="{{ route('admin.orders', 'pending') }}"
                       style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        <i class="feather-arrow-right" style="color: white; font-size: 0.9rem;"></i>
                    </a>
                </div>
                <div style="color: white; font-size: 2rem; font-weight: 800; line-height: 1;">
                    {{ number_format($stats['totalOrders']) }}
                </div>
                <div style="color: rgba(255,255,255,0.75); font-size: 0.82rem; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.8px;">
                    Total Orders
                </div>
            </div>
            <div style="padding: 0.85rem 1.5rem; background: white; display: flex; align-items: center; gap: 6px;">
                <i class="feather-shopping-bag" style="color: #f59e0b; font-size: 0.8rem;"></i>
                <span style="font-size: 0.78rem; color: #64748b;">All time order count</span>
            </div>
        </div>
    </div>
</div>

{{-- Books --}}
<div class="col-xxl-3 col-md-6">
    <div class="card stretch stretch-full" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07);">
        <div class="card-body p-0">
            <div style="background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="feather-book" style="color: white; font-size: 1.3rem;"></i>
                    </div>
                    <a href="{{ route('admin.books.index') }}"
                       style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        <i class="feather-arrow-right" style="color: white; font-size: 0.9rem;"></i>
                    </a>
                </div>
                <div style="color: white; font-size: 2rem; font-weight: 800; line-height: 1;">
                    {{ number_format($stats['totalBooks']) }}
                </div>
                <div style="color: rgba(255,255,255,0.75); font-size: 0.82rem; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.8px;">
                    Total Books
                </div>
            </div>
            <div style="padding: 0.85rem 1.5rem; background: white; display: flex; align-items: center; gap: 6px;">
                <i class="feather-book-open" style="color: #0ea5e9; font-size: 0.8rem;"></i>
                <span style="font-size: 0.78rem; color: #64748b;">Books in catalog</span>
            </div>
        </div>
    </div>
</div>

{{-- Categories --}}
<div class="col-xxl-3 col-md-6">
    <div class="card stretch stretch-full" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07);">
        <div class="card-body p-0">
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 1.5rem;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                        <i class="feather-tag" style="color: white; font-size: 1.3rem;"></i>
                    </div>
                    <a href="{{ route('admin.categories.index') }}"
                       style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none;">
                        <i class="feather-arrow-right" style="color: white; font-size: 0.9rem;"></i>
                    </a>
                </div>
                <div style="color: white; font-size: 2rem; font-weight: 800; line-height: 1;">
                    {{ number_format($stats['totalCategories']) }}
                </div>
                <div style="color: rgba(255,255,255,0.75); font-size: 0.82rem; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.8px;">
                    Total Categories
                </div>
            </div>
            <div style="padding: 0.85rem 1.5rem; background: white; display: flex; align-items: center; gap: 6px;">
                <i class="feather-layers" style="color: #10b981; font-size: 0.8rem;"></i>
                <span style="font-size: 0.78rem; color: #64748b;">Book categories</span>
            </div>
        </div>
    </div>
</div>