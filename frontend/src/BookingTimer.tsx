import { Button } from "@/components/ui/button";
import { Body17, BookingStatus, Bookings, User } from "./api-client";
import { useTimer } from "react-timer-hook";
import { useRecoilState } from "recoil";
import { userAtom } from "./atoms";
import { useEffect } from "react";
import { client } from "./backend";
import Lottie from "react-lottie";
import dataAnimation from "./assets/hourglass.json";
import Confirmation from "@/components/ui/confirmation";

const Animation = () => {
  const defaultOptions = {
    loop: true,
    autoplay: true,
    animationData: dataAnimation,
    rendererSettings: {
      preserveAspectRatio: "xMidYMid slice",
    },
  };

  return <Lottie options={defaultOptions} height={40} width={40} />;
};

export default Animation;

export const BookingTimer = () => {
  const [user, setUser] = useRecoilState(userAtom);

  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );

  const { days, minutes, hours, restart } = useTimer({
    expiryTimestamp: new Date(),
    autoStart: false,
  });

  useEffect(() => {
    if (activeBooking) {
      restart(new Date(activeBooking.end_date!));
    }
  }, [activeBooking]);

  if (!activeBooking) {
    return <></>;
  }

  const cancelBooking = async () => {
    await client.cancelBooking(
      new Body17({
        id: activeBooking!.id,
      })
    );
    setUser(
      new User({
        ...user,
        bookings: [
          ...user.bookings!.filter((x) => x !== activeBooking),
          new Bookings({
            ...activeBooking,
            status: BookingStatus.UnBooked,
            end_date: new Date().toISOString(),
          }),
        ],
      })
    );
  };

  return (
    <div className="flex flex-col justify-center items-center content-center px-2 py-2 my-4 font-semibold bg-white rounded-xl">
      <div>
        <Animation />
      </div>
      <div className="text-center text-lg mb-2">
        До конца бронирования осталось:
        <div>{`${hours}ч:${minutes}м`}</div>
      </div>
      <div className="w-1/2 mb-2">
        <Confirmation
          title="Отмена бронирования. Хотите продолжить?"
          type="red"
          accept={cancelBooking}
          cancel={() => {}}
          trigger={<Button variant="reject">Отменить</Button>}
        />
      </div>
    </div>
  );
};
