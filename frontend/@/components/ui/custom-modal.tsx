import React, { useState } from "react";
import { Button } from "./button";

interface CustomModalProps {
  content: JSX.Element;
  cancel: () => void;
  trigger: JSX.Element;
}

const CustomModal = ({ content, cancel, trigger }: CustomModalProps) => {
  const [isOpen, setIsOpen] = useState(false);

  const handleCancel = () => {
    cancel();
    setIsOpen(false);
  };

  return (
    <>
      {isOpen && (
        <div className="fixed top-0 left-0 z-[51] flex flex-col items-center justify-center w-full h-full bg-white overflow-y-auto ">
          <div className="absolute pb-10 top-10 ">
            {content}
            <div
              className="fixed bottom-0 left-0 flex w-full p-2 space-x-2 bg-white {
          }"
            >
              <Button onClick={handleCancel}>Назад</Button>
            </div>
          </div>
        </div>
      )}
      <div className="w-1/2" onClick={() => setIsOpen(true)}>
        {trigger}
      </div>
    </>
  );
};

export default CustomModal;
