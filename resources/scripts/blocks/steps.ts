import Swiper from 'swiper';
import 'swiper/css';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('initStepsSlider', () => {
        return {
            swiper: null as Swiper | null,

            init() {
                this.swiper = new Swiper(this.$refs.swiperContainer, {
                    slidesPerView: 1.2,
                    spaceBetween: 12,
                    rewind: true,
                    breakpoints: {
                        768: {
                            spaceBetween: 12,
                            slidesPerView: 2.2,
                        },
                        1024: {
                            spaceBetween: 24,
                            slidesPerView: 3,
                        },
                    },
                });
            },
        };
    });
});
