<div class="form-actions">
    <div class="actions-primary">
        <button type="submit" class="btn btn-primary">
            {{ $submitLabel ?? 'Simpan' }}
        </button>
        <a href="{{ $cancelUrl }}" class="btn btn-secondary">
            Batal
        </a>
    </div>

    @if($showDelete)
        <div class="actions-danger">
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                Hapus
            </button>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 400px;">
                <h3>{{ $deleteTitle ?? 'Hapus?' }}</h3>
                <p>{{ $deleteMessage ?? 'Tindakan ini tidak dapat dibatalkan.' }}</p>
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary" style="margin-right: 10px;">
                        Batal
                    </button>
                    <form id="deleteForm" method="POST" action="{{ $deleteUrl }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete() {
                document.getElementById('deleteModal').style.display = 'block';
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').style.display = 'none';
            }

            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) closeDeleteModal();
            });
        </script>
    @endif
</div>

<style>
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #eee;
        margin-top: 20px;
    }

    .actions-primary {
        display: flex;
        gap: 10px;
    }

    .actions-danger {
        display: flex;
        gap: 10px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.2s;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #545b62;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }
</style>
