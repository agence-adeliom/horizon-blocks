import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
// Uncomment when problem from Swiper lib fixed
// import { SwiperOptions } from "swiper/types";
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initPhotoCarousel', () => {
        return {
            open: false,
            init() {
                // Lightbox swiper
                // Uncomment when problem from Swiper lib fixed
                // const swiperParams: SwiperOptions = {
                const swiperParams = {
                    modules: [Navigation],
                    slidesPerView: 1,
                    loop: false,
                    navigation: {
                        nextEl: this.$refs.buttonNext,
                        prevEl: this.$refs.buttonPrev,
                        disabledClass: 'opacity-50 pointer-events-none',
                    },
                };
                this.swiper = new Swiper(this.$refs.swiperContainer, swiperParams);

                // Inline slider
                if (this.$refs.horizontalContainer) {
                    const horizontalParams = {
                        modules: [Navigation, Pagination],
                        slidesPerView: 1,
                        spaceBetween: 16,
                        loop: false,
                        navigation: {
                            nextEl: this.$refs.horizontalNext,
                            prevEl: this.$refs.horizontalPrev,
                            disabledClass: 'opacity-50 pointer-events-none',
                        },
                        pagination: {
                            el: this.$refs.progressBar,
                            type: 'progressbar',
                            progressbarFillClass: 'swiper-pagination-progressbar-fill !bg-primary',
                        },
                        on: {
                            slideChange: (sw: any) => {
                                if (!this.$refs.captionContainer) return;
                                const captions = this.$refs.captionContainer.querySelectorAll('.swiper-caption');
                                captions.forEach((el: HTMLElement) => {
                                    const idx = parseInt(el.dataset.captionIndex || '0', 10);
                                    el.classList.toggle('hidden', idx !== sw.realIndex);
                                });
                            },
                        },
                    };
                    this.horizontalSwiper = new Swiper(this.$refs.horizontalContainer, horizontalParams);
                }

                this.$nextTick(() => {
                    this.swiper.init();
                    if (this.horizontalSwiper) {
                        this.horizontalSwiper.init();
                    }
                });
            },
        };
    });
});
