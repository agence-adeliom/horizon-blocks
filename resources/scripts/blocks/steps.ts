import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initStepsSlider', () => {
        return {
            init() {
                const swiperParams = {
                    // configure Swiper to use modules
                    modules: [Navigation],
                    slidesPerView: 1.2,
                    spaceBetween: '12',
                    loop: false,
                    mousewheel: true,
                    navigation: {
                        nextEl: this.$refs.buttonNext,
                        prevEl: this.$refs.buttonPrev,
                        disabledClass: 'opacity-0 transition-all pointer-events-none',
                    },
                    breakpoints: {
                        768: {
                            spaceBetween: '12',
                            slidesPerView: 2.2,
                        },
                        1024: {
                            spaceBetween: '24',
                            slidesPerView: 3,
                        },
                        1280: {
                            slidesPerView: 3,
                        },
                    },
                };
                this.swiper = new Swiper(this.$refs.swiperContainer, swiperParams);

                this.$nextTick(() => {
                    this.swiper.init();
                });
            },
        };
    });
});
