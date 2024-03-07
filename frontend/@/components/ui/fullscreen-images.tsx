import React, { useState, useRef, useEffect } from "react";
import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import { Button } from "./button";

interface FullScreenImagesProps {
  images: string[];
  fullscreenIndex: number;
  trigger: JSX.Element;
}

const FullScreenImages = ({
  images,
  fullscreenIndex,
  trigger,
}: FullScreenImagesProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const sliderRef = useRef<Slider>(null);
  const isTransitioning = useRef(false);
  const [isOpen, setIsOpen] = useState(false);

  const settings = {
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    initialSlide: fullscreenIndex,
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
      {isOpen && (
        <div className="fixed top-0 left-0 z-[53] w-full h-full bg-black bg-opacity-95">
          <div className="relative flex flex-col justify-center h-full m-auto">
            <Slider ref={sliderRef} {...settings}>
              {images.map((image, index) => (
                <div className="md:h-screen md:w-screen" key={index}>
                  <img
                    onClick={() => setIsOpen(false)}
                    src={image}
                    alt={`Slide ${index}`}
                    className="object-contain h-auto m-auto sm:min-w-full md:h-screen md:w-screen"
                  />
                </div>
              ))}
            </Slider>
            <div className="flex justify-center px-1 py-1 mt-2 sm:m-auto sm:space-x-2 -bottom-20 sm:justify-start sm:w-1/2 md:hidden">
              {images.map((x, i) => (
                <div
                  key={`image_${i}`}
                  className={`w-full flex items-center bg-white rounded-xl transition-all h-14 ${
                    i === activeIndex
                      ? "shadow border-2 border-yellow"
                      : "scale-90"
                  }`}
                  onClick={() => handlePaginationClick(i)}
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
                className="mx-auto max-w-[250px]"
                onClick={() => setIsOpen(false)}
              >
                Назад
              </Button>
            </div>
          </div>
        </div>
      )}
      <div className="" onClick={() => setIsOpen(true)}>
        {trigger}
      </div>
    </>
  );
};

export default FullScreenImages;
