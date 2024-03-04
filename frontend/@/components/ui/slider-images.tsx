import React, { useState, useRef } from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

interface SliderImagesProps {
  images: string[];
}

const SliderImages = ({ images }: SliderImagesProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const sliderRef = useRef<Slider>(null);
  const isTransitioning = useRef(false);

  const settings = {
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    slidesToScroll: 1,
    beforeChange: (current, next) => {
      setActiveIndex(next);
      isTransitioning.current = true;
    },
    afterChange: () => {
      isTransitioning.current = false;
    },
  };

  const handlePaginationClick = (index: number) => {
    setActiveIndex(index);
    if (sliderRef.current) {
      sliderRef.current.slickGoTo(index);
      isTransitioning.current = true;
    }
  };

  return (
    <div className="relative h-64 sm:h-80">
      <Slider ref={sliderRef} {...settings}>
        {images.map((image, index) => (
          <div key={index}>
            <img
              src={image}
              alt={`Slide ${index}`}
              className="object-cover h-64 rounded-xl sm:min-w-full sm:h-80"
            />
          </div>
        ))}
      </Slider>
      <div className="absolute bottom-0 flex justify-center px-1 py-1 mt-2 sm:justify-start sm:w-1/2">
        {images.map((x, i) => (
          <div
            key={`image_${i}`}
            className={`w-full flex items-center bg-white rounded-xl transition-all h-14 ${
              i === activeIndex ? "shadow border-2 border-yellow" : "scale-90"
            }`}
            onClick={() => handlePaginationClick(i)}
            style={{
              cursor: isTransitioning.current ? "not-allowed" : "pointer",
            }}
          >
            <img
              className="object-cover w-full h-full rounded-xl"
              src={x}
              alt=""
            />
          </div>
        ))}
      </div>
    </div>
  );
};

export default SliderImages;
