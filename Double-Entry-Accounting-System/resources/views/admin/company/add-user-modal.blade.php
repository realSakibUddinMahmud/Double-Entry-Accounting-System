<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign User to <span id="modalCompanyName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignUserForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50px">Select</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                <!-- Users will load here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="paginationContainer" class="mt-3 text-center"></div>
                </div>
                <input type="hidden" name="user_id" id="selectedUserId">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="assignBtn" disabled>Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addUserModal');
    let currentCompanyId;
    let currentPage = 1;
    let searchQuery = '';
    let searchDebounce;

    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            currentCompanyId = button.getAttribute('data-company-id');
            document.getElementById('modalCompanyName').textContent = button.getAttribute('data-company-name');
            document.getElementById('assignUserForm').action = `/companies/${currentCompanyId}/assign-user`;
            loadUsers();
        });
    }

    // Search functionality
    document.getElementById('userSearch').addEventListener('input', function(e) {
        clearTimeout(searchDebounce);
        searchQuery = e.target.value;
        currentPage = 1;
        searchDebounce = setTimeout(() => {
            loadUsers();
        }, 300);
    });

    // // Search button click
    // document.getElementById('searchButton').addEventListener('click', function() {
    //     currentPage = 1;
    //     loadUsers();
    // });

    function loadUsers(page = 1) {
        currentPage = page;
        const url = `/companies/${currentCompanyId}/get-users?page=${page}&search=${encodeURIComponent(searchQuery)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('userTableBody');
                tbody.innerHTML = '';
                
                if (data.data.length > 0) {
                    data.data.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><input type="radio" name="user_radio" value="${user.id}"></td>
                            <td>${user.name}</td>
                            <td>${user.phone || '-'}</td>
                            <td>${user.email}</td>
                        `;
                        row.querySelector('input').addEventListener('change', function() {
                            document.getElementById('selectedUserId').value = this.value;
                            document.getElementById('assignBtn').disabled = false;
                        });
                        tbody.appendChild(row);
                    });
                    
                    // Pagination
                    const pagination = document.getElementById('paginationContainer');
                    pagination.innerHTML = `
                        <nav>
                            <ul class="pagination justify-content-center">
                                ${data.links.map(link => `
                                    <li class="page-item ${link.active ? 'active' : ''} ${link.url ? '' : 'disabled'}">
                                        <a class="page-link" href="#" data-page="${link.url ? new URL(link.url).searchParams.get('page') || 1 : ''}">
                                            ${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                                        </a>
                                    </li>
                                `).join('')}
                            </ul>
                        </nav>
                    `;
                    
                    pagination.querySelectorAll('.page-link').forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (link.dataset.page) {
                                loadUsers(link.dataset.page);
                            }
                        });
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No users found</td></tr>';
                    document.getElementById('paginationContainer').innerHTML = '';
                }
            });
    }
});
</script>