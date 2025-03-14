import Swiper from "swiper";
import {Navigation} from "swiper/modules";
// Uncomment when problem from Swiper lib fixed
// import { SwiperOptions } from "swiper/types";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initLogosSlider", () => {
    return {
      activeLogo: null,
      init() {
        const swiperParams = {
          modules: [Navigation],
          slidesPerView: 2,
          spaceBetween: "12",
          loop: false,
          mousewheel: true,
          navigation: {
            nextEl: this.$refs.buttonNext,
            prevEl: this.$refs.buttonPrev,
            disabledClass: "opacity-25 pointer-events-none transition-all",
          },
          breakpoints: {
            768: {
              slidesPerView: 3,
            },
            1024: {
              spaceBetween: "24",
              slidesPerView: 4,
            },
            1280: {
              spaceBetween: "24",
              slidesPerView: 5,
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
