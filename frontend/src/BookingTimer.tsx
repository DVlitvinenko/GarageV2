import { Button } from "@/components/ui/button";
import { Body17, BookingStatus, Bookings, User } from "./api-client";
import { useTimer } from "react-timer-hook";
import { useRecoilState } from "recoil";
import { userAtom } from "./atoms";
import { useEffect } from "react";
import { client } from "./backend";

export const BookingTimer = () => {
  const [user, setUser] = useRecoilState(userAtom);

  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );

  const { minutes, hours, restart } = useTimer({
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
    <div className="flex items-center content-center px-4 py-2 mt-2 space-x-2 text-lg font-bold bg-white rounded-xl">
      <div className="flex flex-col items-center content-center">
        Осталось времени:
        <span className="text-xl">{`${hours}ч:${minutes}м`}</span>
      </div>
      <Button variant="reject" onAsyncClick={cancelBooking}>
        Отменить бронь
      </Button>
    </div>
  );
};
