import React, { useState, useRef } from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import { Button } from "./button";
import FullScreenImages from "@/components/ui/fullscreen-images";

interface SliderImagesProps {
  images: string[];
  classImages: string;
  classPaginationImages: string;
  openIsAffordable: boolean;
}

const SliderImages = ({
  images,
  classImages,
  classPaginationImages,
  openIsAffordable = false,
}: SliderImagesProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const sliderRef = useRef<Slider>(null);
  const isTransitioning = useRef(false);
  const [fullscreenIndex, setFullscreenIndex] = useState(-1);

  const settings = {
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    beforeChange: (current, next) => {
      setActiveIndex(next);
      isTransitioning.current = true;
    },
    afterChange: (current) => {
      setFullscreenIndex(current);
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
    <>
      <div className={`relative h-64 sm:h-80 ${classImages}`}>
        {!openIsAffordable && (
          <Slider ref={sliderRef} {...settings}>
            {images.map((image, index) => (
              <div key={index}>
                <img
                  src={image}
                  alt={`Slide ${index}`}
                  className={`bg-black bg-opacity-90 object-cover w-full h-64 rounded-xl sm:min-w-full sm:h-80 ${classImages}`}
                />
              </div>
            ))}
          </Slider>
        )}
        <FullScreenImages
          trigger={
            openIsAffordable ? (
              <Slider ref={sliderRef} {...settings}>
                {images.map((image, index) => (
                  <div key={index}>
                    <img
                      src={image}
                      alt={`Slide ${index}`}
                      className={`bg-black bg-opacity-90 object-cover w-full h-64 rounded-xl sm:min-w-full sm:h-80 ${classImages}`}
                    />
                  </div>
                ))}
              </Slider>
            ) : (
              <div className="hidden"></div>
            )
          }
          fullscreenIndex={fullscreenIndex}
          images={images}
        />
        <div
          className={`absolute bottom-0 flex justify-center px-1 py-1 mt-2 sm:justify-start sm:w-1/2 ${classPaginationImages}`}
        >
          {images.map((x, i) => (
            <div
              key={`image_${i}`}
              className={`w-full flex items-center bg-white rounded-xl transition-all h-14 ${
                i === activeIndex ? "shadow border-2 border-yellow" : "scale-90"
              } `}
              onClick={() => handlePaginationClick(i)}
              onMouseEnter={() => handlePaginationClick(i)}
              style={{
                cursor: isTransitioning.current ? "default" : "default",
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
    </>
  );
};

export default SliderImages;
