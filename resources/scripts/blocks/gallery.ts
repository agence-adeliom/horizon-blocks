import Swiper from 'swiper';
import 'swiper/css';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initGallery', () => {
        return {
            open: false,
            announcement: '',
            trigger: null as HTMLElement | null,
            swiper: null as Swiper | null,
            horizontalSwiper: null as Swiper | null,

            init() {
                // Popin : une image à la fois, les images masquées sont retirées du parcours et de la restitution
                this.swiper = new Swiper(this.$refs.swiperContainer, {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    rewind: true,
                    observer: true,
                    observeParents: true,
                    on: {
                        init: (swiper) => this.updateSlides(swiper),
                        slideChange: (swiper) => {
                            this.updateSlides(swiper);
                            this.announcement = swiper.slides[swiper.activeIndex]?.dataset.announcement ?? '';
                        },
                    },
                });

                if (this.$refs.horizontalContainer) {
                    this.horizontalSwiper = new Swiper(this.$refs.horizontalContainer, {
                        slidesPerView: 1.2,
                        spaceBetween: 16,
                        rewind: true,
                        watchSlidesProgress: true,
                        breakpoints: {
                            1024: {
                                slidesPerView: 3.2,
                                spaceBetween: 24,
                            },
                        },
                    });
                }
            },

            updateSlides(swiper: Swiper) {
                swiper.slides.forEach((slide, i) => (slide.inert = i !== swiper.activeIndex));
            },

            // Une vignette qui reçoit le focus clavier hors de la zone visible fait défiler le slider jusqu'à elle
            showHorizontalSlide(index: number) {
                const slide = this.horizontalSwiper?.slides[index];

                if (slide && !slide.classList.contains('swiper-slide-fully-visible')) {
                    this.horizontalSwiper.slideTo(index);
                }
            },

            openAt(index: number) {
                this.trigger = document.activeElement as HTMLElement;
                this.open = true;

                this.$nextTick(() => {
                    this.swiper?.update();
                    this.swiper?.slideTo(index, 0);
                    this.updateSlides(this.swiper);
                });
            },

            close() {
                this.open = false;
                this.announcement = '';

                // Retour du focus sur la vignette qui a ouvert la popin
                this.$nextTick(() => this.trigger?.focus());
            },
        };
    });
});
