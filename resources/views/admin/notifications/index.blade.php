@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-700">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            @forelse(auth()->user()->notifications as $notification)
                <div class="p-6 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50' }}">
                    <div class="flex items-start gap-4">
                        {{-- Icon based on type --}}
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0
                                                            {{ $notification->data['type'] === 'retroactive_overtime_approval' ? 'bg-orange-100' : 'bg-red-100' }}">
                            @if($notification->data['type'] === 'retroactive_overtime_approval')
                                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            @endif
                        </div>

                        <div class="flex-1">
                            {{-- Title --}}
                            <h3 class="text-sm font-bold text-gray-900 mb-1">
                                @if($notification->data['type'] === 'retroactive_overtime_approval')
                                    ⚠️ Retroactive Overtime Approval
                                @else
                                    🔒 Overtime Approval Blocked (Payroll Locked)
                                @endif
                            </h3>

                            {{-- Details --}}
                            <div class="space-y-1 text-sm text-gray-600">
                                <p><span class="font-medium">Employee:</span> {{ $notification->data['employee_name'] }}</p>
                                <p><span class="font-medium">Work Date:</span> {{ $notification->data['work_date'] }}</p>
                                <p><span class="font-medium">Approved Hours:</span> {{ $notification->data['approved_hours'] }}
                                    hours</p>

                                @if($notification->data['type'] === 'retroactive_overtime_approval')
                                    <p class="text-orange-600 font-semibold">
                                        Approved {{ $notification->data['days_late'] }} days after work date
                                    </p>
                                @else
                                    <p class="text-red-600 font-semibold">
                                        {{ $notification->data['action_required'] }}
                                    </p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-3 mt-4">
                                <a href="{{ url('/hris/attendance/overtime/' . $notification->data['overtime_request_id']) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                                    View Details
                                </a>

                                @if(!$notification->read_at)
                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-500 hover:text-gray-700">
                                            Mark as read
                                        </button>
                                    </form>
                                @endif
                            </div>

                            {{-- Timestamp --}}
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-gray-500 font-medium">No notifications</p>
                    <p class="text-sm text-gray-400 mt-1">You're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection