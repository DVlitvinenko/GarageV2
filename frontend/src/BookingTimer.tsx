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

export const BookingTimer = () => {
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
    </>
  );
};
