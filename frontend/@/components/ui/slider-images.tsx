import React, { useState, useRef } from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import { Button } from "./button";

interface SliderImagesProps {
  images: string[];
}

const SliderImages = ({ images }: SliderImagesProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const sliderRef = useRef<Slider>(null);
  const isTransitioning = useRef(false);
  const [isCkicked, setIsCkecked] = useState(false);

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
    <>
      <div className="relative h-64 sm:h-80">
        <Slider ref={sliderRef} {...settings}>
          {images.map((image, index) => (
            <div key={index}>
              <img
                onClick={() => setIsCkecked(!isCkicked)}
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
      {isCkicked && (
        <div className="fixed top-0 left-0 z-[53] w-full h-full bg-black bg-opacity-95">
          <div className="relative flex flex-col justify-center h-full sm:h-80">
            <Slider ref={sliderRef} {...settings}>
              {images.map((image, index) => (
                <div key={index}>
                  <img
                    onClick={() => setIsCkecked(!isCkicked)}
                    src={image}
                    alt={`Slide ${index}`}
                    className="object-contain h-auto m-auto sm:min-w-full sm:h-80"
                  />
                </div>
              ))}
            </Slider>
            <div className="flex justify-center px-1 py-1 mt-2 -bottom-20 sm:justify-start sm:w-1/2">
              {images.map((x, i) => (
                <div
                  key={`image_${i}`}
                  className={`w-full flex items-center bg-white rounded-xl transition-all h-14 ${
                    i === activeIndex
                      ? "shadow border-2 border-yellow"
                      : "scale-90"
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
            <div className="fixed bottom-0 flex w-full p-2">
              {" "}
              <Button
                className="mx-auto"
                onClick={() => setIsCkecked(!isCkicked)}
              >
                Назад
              </Button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default SliderImages;
