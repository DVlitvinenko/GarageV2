import React, { useRef, useState } from "react";

interface SliderImagesProps {
  images: string[];
}

const SliderImages = ({ images }: SliderImagesProps) => {
  const [activeIndex, setActiveIndex] = useState(0);
  const containerRef = useRef(null);

  const handleDotClick = (index) => {
    setActiveIndex(index);
    if (containerRef.current) {
      containerRef.current.scrollTo({
        left: index * containerRef.current.offsetWidth,
        behavior: "smooth",
      });
    }
  };
  const handleScroll = () => {
    if (containerRef.current) {
      const containerWidth = containerRef.current.offsetWidth;
      const scrollLeft = containerRef.current.scrollLeft;
      const index = Math.round(scrollLeft / containerWidth);
      setActiveIndex(index);
    }
  };

  return (
    <div className="relative h-64">
      <div
        className={`absolute flex items-center justify-start h-64 space-x-1 pr-1 overflow-scroll overflow-x-auto scrollbar-hide`}
        ref={containerRef}
        onScroll={handleScroll}
      >
        {images.map((x, i) => (
          <img
            key={`image_${i}`}
            className="object-cover h-64 rounded-xl"
            src={x}
            alt={`Slider Image ${i}`}
          />
        ))}
      </div>
      <div className="absolute bottom-0 flex justify-center px-1 py-1 mt-2">
        {images.map((x, i) => {
          return (
            <div
              key={`image_${i}`}
              className={`w-full flex items-center bg-white rounded-xl transition-all h-14  ${
                i === activeIndex
                  ? "shadow border-2 border-yellow"
                  : " scale-90"
              }`}
            >
              <img
                className="object-cover w-full h-full rounded-xl"
                src={x}
                onClick={() => handleDotClick(i)}
                alt=""
              />
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default SliderImages;