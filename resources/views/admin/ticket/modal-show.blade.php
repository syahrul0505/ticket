<div class="modal fade modal-notification" id="tabs-{{ $ticket->id }}-show-ticket" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form class="mt-0 modal-content" action="{{ route('tickets.update-comment', $ticket->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <!-- Icon Header -->
                <div class="d-flex justify-content-center">
                    <div class="icon-content m-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Modal Title -->
                <div class="text-center mb-4 mt-3">
                    <h4 class="mb-0">Ticket Comments</h4>
                </div>

                <!-- Ticket Information Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-2">{{ $ticket->title }}</h5>
                                <p class="text-muted mb-2">
                                    <strong>Category:</strong> {{ $ticket->problem_category }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge 
                                    @if($ticket->status == 'open') bg-info
                                    @elseif($ticket->status == 'in_progress') bg-warning
                                    @elseif($ticket->status == 'resolved') bg-success
                                    @elseif($ticket->status == 'close') bg-secondary
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="mt-3">
                    <h6 class="mb-3">Comments History</h6>
                    
                    @if($ticket->ticketComments && $ticket->ticketComments->count() > 0)
                        <div class="comments-container mb-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach($ticket->ticketComments as $comment)
                                <div class="comment-item mb-3 p-3 bg-dark rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="badge text-light bg-primary rounded-circle" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    {{ substr($comment->user, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 text-light">{{ $comment->user }}</h6>
                                                <small class="text-muted text-light">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-body ps-5">
                                        <p class="mb-0 text-light">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-dark-warning text-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-circle mb-2">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                            <p class="mb-0">No comments yet. Be the first to comment!</p>
                        </div>
                    @endif

                    <!-- Add Comment Form -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label for="status" class="form-label">Update Status</label>
                                <select name="status" class="form-select" id="status">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="close" {{ $ticket->status == 'close' ? 'selected' : '' }}>Close</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <label for="comment" class="form-label">Add Comment</label>
                                <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Write your comment here..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-light-dark" type="button" data-bs-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x me-1">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Close
                </button>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send me-1">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    Add Comment
                </button>
            </div>
        </form>
    </div>
</div>