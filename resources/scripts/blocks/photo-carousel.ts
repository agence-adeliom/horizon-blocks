import Swiper from 'swiper';
import { Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initPhotoCarousel', () => {
        return {
            open: false,
            announcement: '',
            inlineAnnouncement: '',
            trigger: null as HTMLElement | null,
            swiper: null as Swiper | null,
            horizontalSwiper: null as Swiper | null,

            init() {
                // Popin : une photo à la fois, les photos masquées sont retirées du parcours et de la restitution
                this.swiper = new Swiper(this.$refs.swiperContainer, {
                    slidesPerView: 1,
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

                // Carrousel dans la page : même principe, la légende suit la photo active
                this.horizontalSwiper = new Swiper(this.$refs.horizontalContainer, {
                    modules: [Pagination],
                    slidesPerView: 1,
                    spaceBetween: 16,
                    rewind: true,
                    pagination: {
                        el: this.$refs.progressBar,
                        type: 'progressbar',
                        progressbarFillClass: 'swiper-pagination-progressbar-fill !bg-primary',
                    },
                    on: {
                        init: (swiper) => this.updateSlides(swiper),
                        slideChange: (swiper) => {
                            this.updateSlides(swiper);
                            this.inlineAnnouncement = swiper.slides[swiper.activeIndex]?.dataset.announcement ?? '';
                        },
                    },
                });
            },

            updateSlides(swiper: Swiper) {
                swiper.slides.forEach((slide, i) => (slide.inert = i !== swiper.activeIndex));
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

                // Retour du focus sur la photo qui a ouvert la popin
                this.$nextTick(() => this.trigger?.focus());
            },
        };
    });
});
