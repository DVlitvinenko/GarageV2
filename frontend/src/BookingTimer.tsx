import { Button } from "@/components/ui/button";
import { Body17, Booking, BookingStatus, Bookings, User } from "./api-client";
import { Separator } from "@/components/ui/separator";
import { useTimer } from "react-timer-hook";
import { addDays, addHours, addSeconds, formatDistanceToNow } from "date-fns";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { useRecoilState, useRecoilValue } from "recoil";
import { userAtom } from "./atoms";
import { reject } from "ramda";
import { useEffect, useState } from "react";
import { client } from "./backend";

export const BookingTimer = () => {
  const [user, setUser] = useRecoilState(userAtom);

  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );
  const bookingEndDate = activeBooking
    ? new Date(activeBooking.end_date + "Z")
    : new Date();

  const { minutes, hours, restart } = useTimer({
    expiryTimestamp: bookingEndDate, // Передаем объект Date в useTimer
    // onExpire: () => console.warn("onExpire called"),
  });

  useEffect(() => {
    restart(bookingEndDate);
  }, [user]);

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
          new Bookings({ ...activeBooking, status: BookingStatus.UnBooked }),
        ],
      })
    );
  };

  return (
    <>
      {!!activeBooking && (
        <div className="flex items-center content-center px-4 py-2 mt-2 space-x-2 text-lg font-bold bg-white rounded-xl">
          <div className="flex flex-col items-center content-center">
            Осталось времени:{" "}
            <span className="text-xl">{`${hours}ч:${minutes}м`}</span>
          </div>
          <Button variant="reject" onAsyncClick={cancelBooking}>
            Отменить бронь
          </Button>
        </div>
      )}
    </>
  );
};
