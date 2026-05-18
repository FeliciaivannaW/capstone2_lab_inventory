<div class="form-container" style="max-width: 900px;">
    <form method="POST" action="{{ $action }}" class="form-main">
        @csrf
        @if($method === 'PUT')
            @method('PUT')
        @elseif($method === 'DELETE')
            @method('DELETE')
        @endif
        
        {{ $slot }}
    </form>
</div>

<style>
    .form-container {
        background: white;
        padding: 24px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .form-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: bold;
        margin-bottom: 6px;
        font-size: 14px;
        color: #333;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-group textarea {
        min-height: 80px;
        resize: vertical;
    }

    .form-group.small-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-group.small-2 > div {
        display: flex;
        flex-direction: column;
    }

    .form-error {
        color: #dc3545;
        font-size: 13px;
        margin-top: 4px;
    }
</style>
