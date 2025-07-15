/**
 * Patient Story Slider with GSAP
 * Works with Divi text modules that have specific CSS classes
 * Only shows navigation if corresponding ACF fields are populated
 */

document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on a story post and GSAP is loaded
    if (typeof gsap === 'undefined') {
        console.warn('GSAP not loaded - Story slider will not function');
        return;
    }

    // Check if we have the story slider container
    const sliderContainer = document.querySelector('.story-slide-container');
    if (!sliderContainer) {
        console.log('No .story-slide-container found');
        return;
    }

    // Initialize the story slider
    initStorySlider();
});

function initStorySlider() {
    const sliderContainer = document.querySelector('.story-slide-container');

    // Get all potential slide elements within the container
    const slideOne = sliderContainer.querySelector('.story-slide-one');
    const slideTwo = sliderContainer.querySelector('.story-slide-two');
    const slideThree = sliderContainer.querySelector('.story-slide-three');
    const slideFour = sliderContainer.querySelector('.story-slide-four');

    // Check if we have at least the first slide
    if (!slideOne) {
        console.log('No .story-slide-one found');
        return;
    }

    // Get ACF field data to determine which slides should be active
    // This data is passed from PHP via a global variable
    const storySliderData = window.storySliderData || {};

    // Determine which slides exist, have content modules, AND have populated ACF fields
    const slides = [];

    // Slide one always exists (main content)
    slides.push({
        element: slideOne,
        index: 0,
        slideNumber: 1,
        hasContent: true
    });

    // Check slide two: both module exists AND ACF field is populated
    if (slideTwo && storySliderData.slide_two_populated) {
        slides.push({
            element: slideTwo,
            index: 1,
            slideNumber: 2,
            hasContent: true
        });
    } else if (slideTwo) {
        // Hide the module if ACF field is not populated
        slideTwo.style.display = 'none';
    }

    // Check slide three: both module exists AND ACF field is populated
    if (slideThree && storySliderData.slide_three_populated) {
        slides.push({
            element: slideThree,
            index: 2,
            slideNumber: 3,
            hasContent: true
        });
    } else if (slideThree) {
        // Hide the module if ACF field is not populated
        slideThree.style.display = 'none';
    }

    // Check slide four: both module exists AND ACF field is populated
    if (slideFour && storySliderData.slide_four_populated) {
        slides.push({
            element: slideFour,
            index: 3,
            slideNumber: 4,
            hasContent: true
        });
    } else if (slideFour) {
        // Hide the module if ACF field is not populated
        slideFour.style.display = 'none';
    }

    console.log(`Found ${slides.length} active slides with content`);

    // If we only have one slide, no need for navigation
    if (slides.length <= 1) {
        console.log('Only one slide with content, no navigation needed');
        return;
    }

    // Set up initial state - hide all slides except the first
    slides.forEach((slide, index) => {
        gsap.set(slide.element, {
            opacity: index === 0 ? 1 : 0,
            display: index === 0 ? 'block' : 'none',
            position: 'relative' // Keep ALL slides as relative
        });
    });

    // Create navigation container
    const navContainer = createNavigationContainer();

    // Insert navigation after the slider container
    sliderContainer.parentNode.insertBefore(navContainer, sliderContainer.nextSibling);

    // Initialize slider state
    let currentSlideIndex = 0;

    // Create navigation buttons
    updateNavigation(currentSlideIndex, slides.length, navContainer);

    // Navigation functions
    function goToSlide(targetIndex) {
        console.log('goToSlide called with targetIndex:', targetIndex, 'currentSlideIndex:', currentSlideIndex);

        if (targetIndex === currentSlideIndex) {
            console.log('Target is same as current, no change needed');
            return;
        }

        if (targetIndex < 0) {
            console.log('Target index is negative, cannot go back further');
            return;
        }

        if (targetIndex >= slides.length) {
            console.log('Target index exceeds slides length, cannot go forward further');
            return;
        }

        const currentSlide = slides[currentSlideIndex].element;
        const targetSlide = slides[targetIndex].element;

        console.log('Transitioning from slide', currentSlideIndex, 'to slide', targetIndex);

        // Keep everything position: relative, use display instead
        const tl = gsap.timeline({
            onComplete: () => {
                console.log('Transition complete, updating navigation');
                updateNavigation(targetIndex, slides.length, navContainer);
                currentSlideIndex = targetIndex;
            }
        });

        // Step 1: Fade out current slide (keep position: relative)
        tl.to(currentSlide, {
            opacity: 0,
            duration: 0.3,
            ease: "power2.inOut"
        })
            // Step 2: Hide current slide with display, show target slide
            .set(currentSlide, {
                display: 'none'
            })
            .set(targetSlide, {
                display: 'block',
                opacity: 0,
                position: 'relative' // Ensure it stays relative
            })
            // Step 3: Fade in target slide
            .to(targetSlide, {
                opacity: 1,
                duration: 0.3,
                ease: "power2.inOut"
            });
    }

    // Add click handlers for navigation
    navContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('story-nav-prev') || e.target.closest('.story-nav-prev')) {
            e.preventDefault();
            goToSlide(currentSlideIndex - 1);
        } else if (e.target.classList.contains('story-nav-next') || e.target.closest('.story-nav-next')) {
            e.preventDefault();
            goToSlide(currentSlideIndex + 1);
        }
    });

    // Optional: Add keyboard navigation when container is in focus
    sliderContainer.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            goToSlide(currentSlideIndex - 1);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            goToSlide(currentSlideIndex + 1);
        }
    });

    // Make container focusable for keyboard navigation
    sliderContainer.setAttribute('tabindex', '0');
}

function createNavigationContainer() {
    const navContainer = document.createElement('div');
    navContainer.className = 'story-navigation';
    navContainer.innerHTML = `
        <div class="story-nav-buttons">
            <button class="story-nav-btn story-nav-prev" aria-label="Previous slide" type="button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                </svg>
            </button>
            <span class="story-slide-counter">
                <span class="current-slide">1</span> / <span class="total-slides">1</span>
            </span>
            <button class="story-nav-btn story-nav-next" aria-label="Next slide" type="button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
            </svg>
            </button>
        </div>
    `;
    return navContainer;
}

function updateNavigation(currentIndex, totalSlides, navContainer) {
    const prevBtn = navContainer.querySelector('.story-nav-prev');
    const nextBtn = navContainer.querySelector('.story-nav-next');
    const currentSlideSpan = navContainer.querySelector('.current-slide');
    const totalSlidesSpan = navContainer.querySelector('.total-slides');

    console.log('Updating navigation - currentIndex:', currentIndex, 'totalSlides:', totalSlides);

    // Update counter
    currentSlideSpan.textContent = currentIndex + 1;
    totalSlidesSpan.textContent = totalSlides;

    // IMPORTANT: Only disable if we're at the very first or last slide
    const shouldDisablePrev = currentIndex <= 0;
    const shouldDisableNext = currentIndex >= totalSlides - 1;

    console.log('Should disable prev:', shouldDisablePrev, 'Should disable next:', shouldDisableNext);

    // Update button states
    prevBtn.disabled = shouldDisablePrev;
    nextBtn.disabled = shouldDisableNext;

    // Add/remove disabled class for styling
    prevBtn.classList.toggle('disabled', shouldDisablePrev);
    nextBtn.classList.toggle('disabled', shouldDisableNext);

    // Update ARIA attributes for accessibility
    prevBtn.setAttribute('aria-disabled', shouldDisablePrev);
    nextBtn.setAttribute('aria-disabled', shouldDisableNext);

    console.log('Prev button disabled:', prevBtn.disabled, 'Next button disabled:', nextBtn.disabled);
    console.log('Prev button classes:', prevBtn.className, 'Next button classes:', nextBtn.className);
}