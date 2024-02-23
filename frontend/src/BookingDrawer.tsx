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

  if (!user?.bookings?.length) {
    return <>У вас пока нет бронирований</>;
  }

  return (
    <>
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
