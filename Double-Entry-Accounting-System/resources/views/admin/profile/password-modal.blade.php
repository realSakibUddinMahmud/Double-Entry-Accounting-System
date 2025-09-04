<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('profile.password.update') }}">

                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="text-center mb-4">
                        <small class="text-muted">Password must be at least 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="old_password" class="form-label">Current Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('old_password') is-invalid @enderror" 
                            id="old_password" name="old_password" required>
                        @error('old_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                            id="new_password" name="new_password" required>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirm Password<span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('confirm_new_password') is-invalid @enderror" 
                            id="confirm_new_password" name="confirm_new_password" required>
                        @error('confirm_new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <br>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>