<div class="form-section">
    @if($title)
        <h3 class="section-title">{{ $title }}</h3>
    @endif
    @if($description)
        <p class="section-description">{{ $description }}</p>
    @endif
    <div class="section-content">
        {{ $slot }}
    </div>
</div>

<style>
    .form-section {
        border-top: 1px solid #eee;
        padding-top: 20px;
        margin-top: 20px;
    }

    .form-section:first-child {
        border-top: none;
        margin-top: 0;
        padding-top: 0;
    }

    .section-title {
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 8px 0;
        color: #333;
    }

    .section-description {
        font-size: 13px;
        color: #666;
        margin: 0 0 15px 0;
    }

    .section-content {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
</style>
