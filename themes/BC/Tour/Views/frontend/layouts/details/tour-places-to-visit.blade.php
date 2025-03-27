@php
if (!empty($translation->places)) {
$title = __("Places to Visit");
}
use Modules\Media\Helpers\FileHelper;
@endphp
@if (!empty($title))
<div class="places-visit-section">
    <h3 class="section-title">{{ $title }}</h3>

    <div id="placesCarousel" class="carousel slide carousel-custom" data-bs-ride="false">
        <div class="carousel-inner">
            @php $chunks = array_chunk($translation->places, 2); @endphp
            @foreach ($chunks as $index => $chunk)
            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                <div class="places-grid">
                    @foreach ($chunk as $placeKey => $place)
                    @if (is_array($place) && isset($place['title']) && $placeKey !== '__place_number__')
                    <div class="place-card" data-bs-toggle="modal" data-bs-target="#placeModal{{ $loop->parent->index }}_{{ $loop->index }}">
                        @if(!empty($place['image']))
                        <div class="place-image-container">
                            <img src="{{ FileHelper::url($place['image']) }}" alt="{{ $place['title'] }}" class="place-image">
                            <div class="place-overlay">
                                <h3 class="place-title">{{ $place['title'] }}</h3>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#placesCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#placesCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Modals for full-size images -->
    @foreach ($chunks as $chunkIndex => $chunk)
    @foreach ($chunk as $placeKey => $place)
    @if (is_array($place) && isset($place['title']) && $placeKey !== '__place_number__' && !empty($place['image']))
    <div class="modal fade" id="placeModal{{ $chunkIndex }}_{{ $loop->index }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="modal-image-container">
                        <img src="{{ FileHelper::url($place['image']) }}" alt="{{ $place['title'] }}" class="modal-image">
                    </div>
                    <div class="place-modal-info">
                        <h3 class="place-modal-title">{{ $place['title'] }}</h3>

                    </div>
                    <div class="place-modal-footer">
                        <div class="place-modal-close text-end">
                            <button type="button" class="btn btn-link close-button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
    @endforeach
</div>
@endif

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .places-visit-section {
        margin-top: 30px;
        margin-bottom: 30px;    }

    .places-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .place-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.3s ease;
    }
    .place-card{
        transform:scale(0.98);
    }
    .place-card:hover {
        transform: scale(1);
    }

    .place-image-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 100%;
        /* 1:1 aspect ratio (square) */
    }

    .place-image {
        max-width: 100%;
        max-height: 400px;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        object-fit: cover;
    }

    .place-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-image: linear-gradient(rgba(0, 0, 0, 0.03) 41%, rgba(0, 0, 0, 0.9));
        padding: 15px;
        color: white;
        align-content: end;
        height: 100%;
    }

    .place-title {
        font-size: 24px;
        margin: 0;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
    }


    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 0;
    }

    .modal-header {
        border-bottom: none;
        position: absolute;
        top: 0;
        right: 0;
        z-index: 1050;
        padding: 1rem;
    }

    .btn-close {
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        padding: 0.75rem;
    }

    .modal-image-container {
        width: 100%;
        height: 250px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        background-color: #f0f0f0;
    }

    .modal-image {
        width: 400px;
        height: 250px;
        object-fit: cover;
    }

    .place-modal-info {
        background-color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .place-modal-title {
        font-size: 1.75rem;
        margin: 0;
        font-weight: 600;
        color: #333;
    }

    .close-button {
        color: #0066cc;
        text-decoration: none;
        font-weight: 500;
        padding: 0;
    }

    @media (max-width: 768px) {
        .places-grid {
            grid-template-columns: 1fr;
        }

        .modal-image-container {
            height: auto;
        }

        .modal-image {
            width: 100%;
            height: auto;
            aspect-ratio: 1/1;
        }
    }

    .modal-content {
        max-width: 400px;
        width: 400px;
        z-index: 2;
        position: relative;
        animation: 0.3s ease-out 0s 1 normal none running iNmMyJ;
        transform: translateY(0px);
        margin-bottom: 30px;
    }

    .modal-dialog {
        margin: auto;
    }

    .modal.fade.show {
        align-content: center;
    }

    h3.place-modal-title {
        font-size: 21px;
        font-weight: bold;
        font-stretch: normal;
        font-style: normal;
    }

    button.btn.btn-link.close-button {
        font-size: 14px;
        font-weight: 600;
        color: rgb(2, 100, 122);
        text-decoration: none;
    }

    .place-modal-footer {
        padding: 10px;
    }

    .modal-content {
        /* background: white; */
        border-radius: 8px;
        overflow: hidden;
    }
    h3.place-title {
    font-size: 18px;
    font-weight: bold;
    font-stretch: normal;
    font-style: normal;
    line-height: 1.11;
    letter-spacing: normal;
    color: rgb(252, 252, 252);
    text-align: center;
}

span.carousel-control-next-icon , span.carousel-control-prev-icon {
    width: 30px;
    height: 30px;
    display: block;
    border-radius: 50%;
    text-align: center;
    background-color: rgb(252, 252, 252);
    position: absolute;
    top: 50%;
    z-index: 50;
    box-shadow: rgba(0, 0, 0, 0.75) 0px 0px 1px 0px;
    cursor: pointer;
    background-size: auto;
}

.carousel-control-next, .carousel-control-prev {
}

.carousel-control-next-icon {
    background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000' height='18px' width='18px' ><path d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/></svg>") !important;
}
.carousel-control-prev-icon{
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000' height='18px' width='18px' %3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e") !important;
}
</style>
