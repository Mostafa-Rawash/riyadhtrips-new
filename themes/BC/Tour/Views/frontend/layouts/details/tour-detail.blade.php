<div class="g-header">
    <div class="left">
        <h1>{{$translation->title}}</h1>
        @if($translation->address)
            <p class="address"><i class="fa fa-map-marker"></i>
                {{$translation->address}}
            </p>
        @endif
    </div>
    <div class="right">
        @if(setting_item('tour_enable_review') and $review_score)
            <div class="review-score">
                <div class="head">
                    <div class="left">
                        <span class="head-rating">{{$review_score['score_text']}}</span>
                        <span class="text-rating">{{__("from :number reviews",['number'=>$review_score['total_review']])}}</span>
                    </div>
                    <div class="score">
                        {{$review_score['score_total']}}<span>/5</span>
                    </div>
                </div>
                <div class="foot">
                    {{__(":number% of guests recommend",['number'=>$row->recommend_percent])}}
                </div>
            </div>
        @endif
    </div>
</div>
@if(!empty($row->duration) or !empty($row->category_tour->name) or !empty($row->max_people) or !empty($row->location->name))
    <div class="g-tour-feature">
    <div class="row">
        @if($row->duration)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-wall-clock"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Duration")}}</h4>
                        <p class="value">
                            {{duration_format($row->duration,true)}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if(!empty($row->category_tour->name))
            @php $cat =  $row->category_tour->translate() @endphp
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-beach"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Tour Type")}}</h4>
                        <p class="value">
                            {{$cat->name ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if($row->max_people)
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-travelling"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Group Size")}}</h4>
                        <p class="value">
                            @if($row->max_people > 1)
                                {{ __(":number persons",array('number'=>$row->max_people)) }}
                            @else
                                {{ __(":number person",array('number'=>$row->max_people)) }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
        @if(!empty($row->location->name))
                @php $location =  $row->location->translate() @endphp
            <div class="col-xs-6 col-lg-3 col-md-6">
                <div class="item">
                    <div class="icon">
                        <i class="icofont-island-alt"></i>
                    </div>
                    <div class="info">
                        <h4 class="name">{{__("Location")}}</h4>
                        <p class="value">
                            {{$location->name ?? ''}}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@include('Layout::global.details.gallery')
@if($translation->content)
    <div class="g-overview">
        <h3>{{__("Overview")}}</h3>
        <div class="description">
            <?php echo $translation->content ?>
        </div>
    </div>
@endif
@if($row->category_id == 9)
@include('Tour::frontend.layouts.details.tour-places-to-visit')
@include('Tour::frontend.layouts.details.tour-plans-steps')
@include('Tour::frontend.layouts.details.tour-package-include-exclude')
@elseif($row->category_id != 9)
@include('Tour::frontend.layouts.details.tour-include-exclude')
@endif

@include('Tour::frontend.layouts.details.tour-itinerary')
@include('Tour::frontend.layouts.details.tour-attributes')
@include('Tour::frontend.layouts.details.tour-faqs')
@includeIf("Hotel::frontend.layouts.details.hotel-surrounding")

@if($row->map_lat && $row->map_lng)
<div class="g-location">
    <div class="location-title">
        <h3>{{__("Tour Location")}}</h3>
        @if($translation->address)
            <div class="address">
                <i class="icofont-location-arrow"></i>
                {{$translation->address}}
            </div>
        @endif
    </div>

    <div class="location-map">
        <div id="map_content"></div>
    </div>
</div>
@endif

@if  ($row->category_id  == 9)

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* ===== Base Styles ===== */
:root {
  --primary-color: #2fb38d;
  --text-color: #333;
  --text-light: #666;
  --text-lighter: rgb(150, 150, 150);
  --border-color: #dee2e6;
  --hover-bg: #f8f9fa;
  --active-bg: rgba(13, 110, 253, 0.05);
}

.g-include-exclude {
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 20px;
  color: var(--text-color);
}

/* ===== Tab Styles ===== */
.nav-tabs {
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 0;
}

.nav-tabs .nav-item {
  margin-bottom: -1px;
}

.nav-tabs .nav-link {
  border: none;
  padding: 0.5rem 1rem;
  color: var(--text-lighter);
  transition: all 0.3s ease;
  cursor: pointer;
  font-weight: 600;
}

.nav-tabs .nav-link.active {
  color: var(--text-color);
  border-bottom: 4px solid var(--primary-color);
  background-color: transparent;
}

.nav-tabs .nav-link:hover:not(.active) {
  border-color: #e9ecef #e9ecef var(--border-color);
  background-color: var(--hover-bg);
}

.tab-content {
  padding: 0;
  border: 0;
  background-color: #fff;
}

.tab-pane {
  padding: 20px 0;
}

/* ===== Accordion Styles ===== */
.accordion {
  border-radius: 0;
  position: relative;
}

.accordion-item {
  border-left: 0;
  border-right: 0;
  border-radius: 0 !important;
  position: relative;
  width: 86%;
}

.accordion-item:first-of-type {
  border-top: 0;
}

.accordion-item:last-of-type {
  border-bottom: 0;
}

.accordion-item::before {
  content: "";
  width: 2px;
  height: -webkit-fill-available;
  background-color: var(--primary-color);
  position: absolute;
}

.accordion-parent {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  padding-left: 0;
}

.accordion-header {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-content: center;
}

.accordion-button {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  align-content: center;
  background: transparent;
  padding: 0;
  font-weight: 500;
  font-size: 1rem;
  color: var(--text-color);
  width: 100%;
}

.accordion-button:not(.collapsed) {
  color: #0d6efd;
  background-color: var(--active-bg);
  box-shadow: none;
}

.accordion-button:focus {
  box-shadow: none;
  border-color: rgba(13, 110, 253, 0.25);
}

.accordion-button::after {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232fb38d'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
  background-size: 12px;
  width: 12px;
  height: 12px;
  color: var(--primary-color);
}

.step-day {
  width: 10%;
  margin: 0 15px 0 0 !important;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  padding: 15px 0;
}

.step-title {
  flex: 1;
  color: var(--primary-color);
  padding: 15px 40px 10px 0;
  width: 100%;
  text-align: left;
  font-weight: 600;
  cursor: pointer;
  position: relative;
  border: none;
  font-size: 1rem;
  padding-left: 30px;
}

.step-icon {
  color: var(--primary-color);
  background: white;
  display: inline-block;
  position: absolute;
  border-radius: 50%;
  border: 3px solid var(--primary-color);
  left: -1%;
  top: 16px;
  width: 20px;
  height: 20px;
  z-index: 2;
}

button:not(.collapsed) .fa-circle::before {
  position: absolute;
  top: 50%;
  transform: translate(-50.5%, -45.8%);
  left: 50%;
  color: var(--primary-color);
  font-size: 12px;
}

.accordion-body {
  text-align: left;
  color: var(--text-light);
  font-size: 14px;
  padding: 0 0 0 30px;
  line-height: 1.6;
}

/* ===== Mobile Styles ===== */
@media (max-width: 768px) {
  .accordion-button {
    padding: 12px;
    font-size: 15px;
  }
  
  .accordion-body {
    padding: 12px 12px 12px 40px;
  }
}

.fa-circle:before {
    font-size: 0px;
}
button:not(.collapsed) .fa-circle:before {
    font-size: 12px;
}
 </style>   
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        initializeComponents();
    } catch (error) {
        console.error("Error initializing components:", error);
        setupFallbackComponents();
    }
});

function initializeComponents() {
    // Use Bootstrap components if available
    if (typeof bootstrap !== 'undefined') {
        setupBootstrapTabs();
        setupBootstrapAccordions();
    } else {
        setupFallbackComponents();
    }
}

function setupBootstrapTabs() {
    const tabElms = document.querySelectorAll('#includeTab button, #excludeTab button, #plansTab button');
    
    tabElms.forEach(tabEl => {
        tabEl.addEventListener('click', event => {
            event.preventDefault();
            new bootstrap.Tab(tabEl).show();
        });
    });
}

function setupBootstrapAccordions() {
    const accordionButtons = document.querySelectorAll('.accordion-button');
    
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-bs-target'));
            const isCollapsed = this.classList.contains('collapsed');
            
            this.classList.toggle('collapsed', !isCollapsed);
            this.setAttribute('aria-expanded', isCollapsed);
            target.classList.toggle('show', isCollapsed);
        });
    });
}

function setupFallbackComponents() {
    setupManualTabs();
    setupManualAccordions();
}

function setupManualTabs() {
    const tabButtons = document.querySelectorAll(
        '#includeTab button, #excludeTab button, #plansTab button'
    );
    const tabPanes = document.querySelectorAll(
        '#includeTabContent .tab-pane, #excludeTabContent .tab-pane, #plansTabContent .tab-pane'
    );
    
    tabButtons.forEach(button => {
        button.addEventListener('click', e => {
            e.preventDefault();
            
            // Update all buttons
            // tabButtons.forEach(btn => {
            //     btn.classList.remove('active');
            //     btn.setAttribute('aria-selected', 'false');
            // });
            
            // Update all panes
            // tabPanes.forEach(pane => {
            //     pane.classList.remove('show', 'active');
            // });
            
            // Activate current tab
            button.classList.add('active');
            button.setAttribute('aria-selected', 'true');
            
            const targetId = button.getAttribute('data-bs-target')?.substring(1);
            if (targetId) {
                const targetPane = document.getElementById(targetId);
                targetPane?.classList.add('show', 'active');
            }
        });
    });
}

function setupManualAccordions() {
    const accordionButtons = document.querySelectorAll('.accordion-button');
    
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            const isCollapsed = this.classList.contains('collapsed');
            
            this.classList.toggle('collapsed', !isCollapsed);
            this.setAttribute('aria-expanded', isCollapsed);
            targetPane?.classList.toggle('show', isCollapsed);
        });
    });
}
   
</script>
@endif