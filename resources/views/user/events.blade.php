@include('user.layout.header');
@include('user.layout.navbar');

<!-- Meta Tags -->
@section('meta_title', 'Events')
@section('meta_description',
    'IKF provides organized karate coaching and martial arts programs in schools throughout
    India, all taught by certified instructors and national-level trainers.')


    @php use Illuminate\Support\Str; @endphp
    <main class="container">
        <section class="hero-section">
            <div class="page-header">
                <div class="page-description">
                    <h2>Upcoming Karate Events</h2>
                    <p>Discover exciting karate competitions, tournaments, and training sessions happening across schools in
                        our district. Join us for these amazing martial arts events and showcase your skills.</p>
                </div>
                <h1 class="page-title">Events</h1>
            </div>
        </section>

        <section class="filter-section">
            {{-- Timeframe Tabs (All, Upcoming, Last Events) --}}
            <div style="display: flex; justify-content: center; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="{{ route('user.events', ['timeframe' => 'all', 'category' => request('category', 'all')]) }}" 
                   class="filter-timeframe-btn {{ ($timeframe ?? 'all') === 'all' ? 'active-timeframe' : '' }}">
                    <i class="fas fa-calendar-alt"></i> All Events
                </a>
                <a href="{{ route('user.events', ['timeframe' => 'upcoming', 'category' => request('category', 'all')]) }}" 
                   class="filter-timeframe-btn {{ ($timeframe ?? '') === 'upcoming' ? 'active-timeframe' : '' }}">
                    <i class="fas fa-clock"></i> Upcoming Events
                </a>
                <a href="{{ route('user.events', ['timeframe' => 'last', 'category' => request('category', 'all')]) }}" 
                   class="filter-timeframe-btn {{ in_array($timeframe ?? '', ['last', 'past']) ? 'active-timeframe' : '' }}">
                    <i class="fas fa-history"></i> Last Events (Past)
                </a>
            </div>

            {{-- Category Filter Buttons --}}
            <div class="filter-buttons">
                <button class="filter-btn {{ ($categorySlug ?? 'all') === 'all' ? 'active' : '' }}" data-filter="all">
                    <i class="fas fa-th-large"></i> All Categories
                </button>
                @foreach ($categories as $category)
                    @php $slug = Str::slug($category->name, '-'); @endphp
                    <button class="filter-btn {{ ($categorySlug ?? '') === $slug ? 'active' : '' }}" data-filter="{{ $slug }}">
                        <i class="fas fa-circle-notch"></i> {{ ucfirst($category->name) }}
                    </button>
                @endforeach
            </div>
        </section>

        <section class="events-section">
            <div class="events-grid" id="eventsGrid">
                @forelse($events as $event)
                    @php
                        $isPast = \Carbon\Carbon::parse($event->event_date)->isPast() && !\Carbon\Carbon::parse($event->event_date)->isToday();
                    @endphp
                    <div class="event-card fade-in-up {{ $isPast ? 'past-event-card' : '' }}"
                        data-category="{{ Str::slug($event->category->name ?? 'uncategorized', '-') }}"
                        data-timeframe="{{ $isPast ? 'past' : 'upcoming' }}">

                        <div class="event-date-header" style="display: flex; position: relative;">
                            <div class="event-date"
                                style="flex: 0 0 30%; font-weight: bold; padding-right: 10px; border-right: 1px solid #ccc; text-align: right;">
                                {{ \Carbon\Carbon::parse($event->event_date)->format('d M') }}
                                <div style="font-size: 0.75rem; font-weight: normal; opacity: 0.85;">{{ \Carbon\Carbon::parse($event->event_date)->format('Y') }}</div>
                            </div>
                            <div class="event-venue"
                                style="flex: 0 0 70%; display: flex; align-items: center; padding-left: 10px;">
                                <i class="fas fa-map-marker-alt" style="margin-right: 6px;"></i>
                                <span>{{ $event->venue }}</span>
                            </div>
                        </div>

                        <div class="event-content">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span class="event-category">{{ ucfirst($event->category->name ?? 'N/A') }}</span>
                                @if($isPast)
                                    <span class="badge-past-event"><i class="fas fa-check-circle"></i> Completed</span>
                                @else
                                    <span class="badge-upcoming-event"><i class="fas fa-star"></i> Upcoming</span>
                                @endif
                            </div>
                            <h3 class="event-title">{{ $event->title }}</h3>
                            <div class="event-info">
                                @if (strtolower($event->category->name ?? '') == 'competition')
                                    <div class="event-info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</span>
                                    </div>

                                    {{-- Registration Fee --}}
                                    @if (!empty($event->fee))
                                        <div class="event-info-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span>Registration Fee: ₹{{ number_format($event->fee, 2) }}</span>
                                        </div>
                                    @endif

                                    {{-- Additional Fee --}}
                                    @if ($event->additional_fee != 0)
                                        <div class="event-info-item">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Team Fee: ₹{{ number_format($event->additional_fee, 2) }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <p class="event-description">{{ $event->description }}</p>
                            @if($isPast)
                                <span class="register-btn" style="background: #444; color: #bbb; cursor: default; justify-content: center;">
                                    <i class="fas fa-calendar-check"></i> Event Concluded
                                </span>
                            @else
                                <a href="{{ route('user.form.show', $event->id) }}" class="register-btn">
                                    <i class="fas fa-user-plus"></i> Register Now
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.15);">
                        <i class="fas fa-calendar-times" style="font-size: 2.5rem; color: #888; margin-bottom: 12px;"></i>
                        <p class="text-light" style="font-size: 1.1rem; margin-bottom: 15px;">No events found for this filter selection.</p>
                        <a href="{{ route('user.events') }}" class="filter-timeframe-btn active-timeframe" style="display: inline-block;">
                            View All Events
                        </a>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <style>
        .filter-timeframe-btn {
            background: #1b1b1b;
            color: #ccc;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 0.65rem 1.4rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .filter-timeframe-btn:hover {
            color: #fff;
            border-color: #e63946;
            background: rgba(230, 57, 70, 0.15);
        }
        .filter-timeframe-btn.active-timeframe {
            background: linear-gradient(135deg, #e63946, #b71c1c);
            color: #fff;
            border-color: #e63946;
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
        }
        .badge-past-event {
            font-size: 0.72rem;
            background: rgba(108, 117, 125, 0.25);
            color: #adb5bd;
            border: 1px solid rgba(108, 117, 125, 0.4);
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .badge-upcoming-event {
            font-size: 0.72rem;
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.4);
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .past-event-card {
            opacity: 0.85;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .past-event-card:hover {
            opacity: 1;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const eventCards = document.querySelectorAll('.event-card');
        const urlParams = new URLSearchParams(window.location.search);
        let categoryParam = urlParams.get('category') || 'all';

        if (categoryParam !== 'all') {
            categoryParam = categoryParam.toLowerCase().replace(/\s+/g, '-');
        }

        function filterCategory(category) {
            eventCards.forEach(card => {
                const cat = card.dataset.category;
                if (category === 'all' || cat === category) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            filterButtons.forEach(btn => btn.classList.remove('active'));

            if (category === 'all') {
                const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
                if (allBtn) allBtn.classList.add('active');
            } else {
                const activeBtn = document.querySelector(`.filter-btn[data-filter="${category}"]`);
                if (activeBtn) activeBtn.classList.add('active');
            }
        }

        // Apply on load
        if (categoryParam && categoryParam !== 'all') {
            filterCategory(categoryParam);
        }

        // Filter button click
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const selected = this.dataset.filter;
                filterCategory(selected);
                
                // Update URL parameter without reload
                const currentUrl = new URL(window.location.href);
                if (selected === 'all') {
                    currentUrl.searchParams.delete('category');
                } else {
                    currentUrl.searchParams.set('category', selected);
                }
                window.history.replaceState({}, '', currentUrl);
            });
        });
    });
    </script>

    @include('user.layout.footer')
