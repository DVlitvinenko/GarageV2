import { useState } from "react";
import { Button } from "./button";

interface ConfirmationProps {
  title: string;
  type: "red" | "green";
  cancel: () => void;
  accept: () => void;
  trigger: JSX.Element;
}

const Confirmation = ({
  title,
  type,
  accept,
  cancel,
  trigger,
}: ConfirmationProps) => {
  const [isOpen, setIsOpen] = useState(false);

  const handleAccept = () => {
    accept();
    setIsOpen(false);
  };

  const handleCancel = () => {
    cancel();
    setIsOpen(false);
  };
  return (
    <>
      {isOpen && (
        <div className="fixed top-0 left-0 z-[52] flex items-center justify-center w-full h-full bg-black/50">
          <div className="flex flex-col items-center justify-center max-w-xs px-8 py-4 text-xl bg-white min-w-80 rounded-xl">
            {title}
            <div className="flex w-full mt-2 space-x-2 ">
              <Button
                variant={type === "red" ? "reject" : "default"}
                onClick={handleAccept}
              >
                Да
              </Button>
              <Button onClick={handleCancel}>Нет</Button>
            </div>
          </div>
        </div>
      )}
      <div className="w-full" onClick={() => setIsOpen(true)}>
        {trigger}
      </div>
    </>
  );
};

export default Confirmation;
