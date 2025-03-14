import Swiper from "swiper";
import {Navigation, Pagination} from "swiper/modules";
// Uncomment when problem from Swiper lib fixed
// import { SwiperOptions } from "swiper/types";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initReviewsSlider", () => {
    return {
      init() {
        // Uncomment when problem from Swiper lib fixed
        // const swiperParams: SwiperOptions = {
        const swiperParams = {
          // configure Swiper to use modules
          modules: [Navigation, Pagination],
          slidesPerView: 1.2,
          spaceBetween: "12",
          loop: false,
          mousewheel: true,
          centerInsufficientSlides: true,
          navigation: {
            nextEl: this.$refs.buttonNext,
            prevEl: this.$refs.buttonPrev,
            disabledClass: "opacity-50",
          },
          pagination: {
            el: this.$refs.swiperPagination,
            type: "bullets",
            clickable: true,
            lockClass: "hidden",
            hiddenClass: "hidden",
            bulletActiveClass: "bg-primary",
            bulletClass:
              "bg-neutral-300 w-6 h-1 rounded-full inline-block transition-colors cursor-pointer",
          },
          breakpoints: {
            768: {
              slidesPerView: 2.2,
            },
            1024: {
              spaceBetween: "24",
              slidesPerView: 2.6,
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
