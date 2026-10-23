import Swiper from "swiper";
import "swiper/css";

document.addEventListener("alpine:init", () => {
  window.Alpine.data("initLogosSlider", () => {
    return {
      activeLogo: null as number | null,
      swiper: null as Swiper | null,
      locked: false,

      init() {
        const container = this.$refs.swiperContainer as HTMLElement;

        this.swiper = new Swiper(container, {
          slidesPerView: 2,
          spaceBetween: 12,
          rewind: true,
          breakpoints: {
            768: {
              slidesPerView: 3,
            },
            1024: {
              spaceBetween: 24,
              slidesPerView: 4,
            },
            1280: {
              spaceBetween: 24,
              slidesPerView: 5,
            },
          },
          on: {
            init: (swiper) => (this.locked = swiper.isLocked),
            lock: () => (this.locked = true),
            unlock: () => (this.locked = false),
          },
        });

        container.addEventListener("focusin", (event) => {
          const swiper = this.swiper;
          const slide = (event.target as HTMLElement).closest<HTMLElement>(".swiper-slide");

          if (!swiper || !slide) {
            return;
          }

          container.scrollLeft = 0;

          const index = swiper.slides.indexOf(slide);
          const perView = Number(swiper.params.slidesPerView) || 1;

          if (index < swiper.activeIndex || index >= swiper.activeIndex + perView) {
            swiper.slideTo(index);
          }
        });
      },
    };
  });
});
