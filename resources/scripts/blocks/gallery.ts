import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
// Uncomment when problem from Swiper lib fixed
// import { SwiperOptions } from "swiper/types";
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
document.addEventListener('alpine:init', () => {
    window.Alpine.data('initGallery', () => {
        return {
            open: false,
            init() {
                // Uncomment when problem from Swiper lib fixed
                // const swiperParams: SwiperOptions = {
                const swiperParams = {
                    // configure Swiper to use modules
                    modules: [Navigation, Pagination],
                    slidesPerView: 1,
                    loop: false,
                    mousewheel: true,
                    spaceBetween: 24,
                    navigation: {
                        nextEl: this.$refs.buttonNext,
                        prevEl: this.$refs.buttonPrev,
                        disabledClass: 'opacity-50 pointer-events-none',
                    },
                    pagination: {
                        el: this.$refs.swiperPagination,
                        type: 'bullets',
                        clickable: true,
                        lockClass: 'hidden',
                        hiddenClass: 'hidden',
                        bulletActiveClass: '!bg-neutral-1000',
                        bulletClass: 'clickable-area relative bg-neutral-500 w-5 h-1 inline-block transition-colors cursor-pointer',
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