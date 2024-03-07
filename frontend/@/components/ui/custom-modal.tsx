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
        <div className="fixed top-0 -left-2 z-[51] flex flex-col items-center justify-center w-full  h-full bg-black bg-opacity-50 overflow-y-auto ">
          <div className="absolute top-0 pb-10 mx-auto w-full flex flex-col items-center justify-center bg-white max-w-[800px] ">
            {content}
            <div
              className="fixed bottom-0 left-0 flex w-full p-2 space-x-2 {
          }"
            >
              <Button className="max-w-[250px] mx-auto" onClick={handleCancel}>
                Назад
              </Button>
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
