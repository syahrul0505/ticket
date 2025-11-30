<div class="modal fade modal-notification" id="tabs-{{ $ticket->id }}-edit-ticket" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="mt-0 modal-content" action="{{ route('tickets.update', $ticket->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="icon-content m-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-archive"><polyline points="21 8 21 21 3 21 3 8"></polyline><rect x="1" y="3" width="22" height="5"></rect><line x1="10" y1="12" x2="14" y2="12"></line></svg>
                    </div>
                </div>

                <div class="text-center mb-3 mt-3">
                    <h4 class="mb-0">EDIT TICKET</h4>
                </div>


                <div class="mt-0 row">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="title">Title</label>
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Ex:KG" aria-label="title" id="title" value="{{ $ticket->title ?? old('title') }}">

                            @if($errors->has('title'))
                                <p class="text-danger">{{ $errors->first('title') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" class="form-select form-select-sm" id="priority">
                            <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>low</option>
                            <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>high</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="problem_category">Problem Category</label>
                            <input type="text" name="problem_category" class="form-control form-control-sm" placeholder="Ex:12" aria-label="problem_category" id="problem_category" value="{{ $ticket->problem_category ?? old('problem_category') }}">

                            @if($errors->has('problem_category'))
                                <p class="text-danger">{{ $errors->first('problem_category') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="user_id">User</label>
                            <select class="form-control form-control-sm" name="user" id="user">
                                @foreach ($users as $user)
                                <option value="{{ $user->username }}" {{ ($user->username == $ticket->assigned_to) ? 'selected' : '' }}>{{ $user->fullname }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('user_id'))
                                <p class="text-danger">{{ $errors->first('user_id') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" cols="30" rows="5" class="form-control" placeholder="Ex:Susu describe">{{ $ticket->description ?? old('description') }}</textarea>

                            @if($errors->has('description'))
                                <p class="text-danger">{{ $errors->first('description') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light-dark" type="button" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

