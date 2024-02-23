import { Button } from "@/components/ui/button";
import { Body17, BookingStatus, Bookings } from "./api-client";
import { Separator } from "@/components/ui/separator";
import { useTimer } from "react-timer-hook";
import { addDays, addHours, addSeconds, formatDistanceToNow } from "date-fns";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { useRecoilState } from "recoil";
import { userAtom } from "./atoms";
import { reject } from "ramda";
import { useState } from "react";
import { client } from "./backend";

export const BookingDrawer = () => {
  const [user, setUser] = useRecoilState(userAtom);
  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );

  const bookingEndDate = activeBooking
    ? new Date(activeBooking.end_date!)
    : new Date();

  bookingEndDate.setSeconds(bookingEndDate.getSeconds() + 600);

  const { minutes, hours, seconds } = useTimer({
    expiryTimestamp: bookingEndDate,
    // onExpire: () => console.warn("onExpire called"),
  });

  // formatDistanceToNow;

  if (!user) {
    return <></>;
  }
  if (!user.bookings) {
    return <></>;
  }

  const cancelBooking = async () => {
    await client.cancelBooking(
      new Body17({
        id: activeBooking!.id,
      })
    );
  };

  return (
    <>
      {!!activeBooking && (
        <div className="flex space-x-1">
          <div className="">Осталось времени {`${hours}ч:${minutes}м`}</div>
          <Button variant="reject" onAsyncClick={cancelBooking}>
            Отменить бронь
          </Button>
        </div>
      )}
      {user.bookings.map((booking) => (
        <div
          className="py-8 overflow-y-auto h-[%]"
          key={`booking${booking.id}`}
        >
          <div className="flex space-x-1 ">
            <img
              className="object-cover w-1/3 h-auto rounded-xl"
              src={booking.car!.images![0]}
              alt=""
            />
            <div>
              <p className="font-semibold">{`${booking.car?.brand} ${booking.car?.model}`}</p>
              <Separator />
              <p className="font-semibold">{`Парк: ${booking.car?.division?.park?.park_name}`}</p>
              <Separator />
              <p className="font-semibold">{`Адрес: ${booking.car?.division?.address}`}</p>
              <Separator />
              <p className="font-semibold">{`Тел.: ${booking.car?.division?.park?.phone}`}</p>
            </div>
          </div>
        </div>
      ))}
    </>
  );
};
