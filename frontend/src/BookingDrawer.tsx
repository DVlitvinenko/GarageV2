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
import { useRecoilState, useRecoilValue } from "recoil";
import { userAtom } from "./atoms";
import { reject } from "ramda";
import { useState } from "react";
import { client } from "./backend";
import { Badge } from "@/components/ui/badge";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";

export const BookingDrawer = () => {
  const [user, setUser] = useRecoilState(userAtom);

  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );

  if (!user?.bookings?.length) {
    return <>У вас пока нет бронирований</>;
  }
  const sortedBookings = user?.bookings.slice().sort((a, b) => {
    if (a.id === activeBooking?.id) return -1; // Активное бронирование должно быть первым
    if (b.id === activeBooking?.id) return 1; // Активное бронирование должно быть первым
    return 0; // Остальные бронирования не изменяют порядок
  });
  return (
    <>
      {sortedBookings.map((booking) => (
        <div
          className="overflow-y-auto h-[%] bg-white py-4 px-2 my-2 rounded-xl"
          key={`booking${booking.id}`}
        >
          {booking.status === BookingStatus.Booked && (
            <p className="mb-2 text-xl font-semibold text-center">
              Текущая бронь
            </p>
          )}
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
          {booking.status === BookingStatus.Booked && (
            <>
              <div className="flex flex-wrap items-center justify-start gap-1 my-3 ">
                <Badge variant="card" className="px-0 py-0 bg-grey ">
                  <span className="flex items-center h-full px-2 bg-white rounded-xl">
                    Депозит{" "}
                    {formatRoubles(booking.rent_term!.deposit_amount_total!)}
                  </span>
                  <span className="flex items-center h-full px-2 ">
                    {formatRoubles(booking.rent_term!.deposit_amount_daily!)}
                    /день
                  </span>
                </Badge>
                <Badge variant="card">
                  Комиссия {booking.car?.division?.park?.commission}
                </Badge>
                <Badge variant="card">
                  {getFuelTypeDisplayName(booking.car?.fuel_type)}
                </Badge>
                <Badge variant="card">
                  {getTransmissionDisplayName(booking.car?.transmission_type)}
                </Badge>

                {!!booking.car?.division?.park?.self_employed && (
                  <Badge variant="card">Для самозанятых</Badge>
                )}
                {!!booking.rent_term?.is_buyout_possible && (
                  <Badge variant="card">Выкуп автомобиля</Badge>
                )}
              </div>
              <div className="flex flex-wrap gap-1 pb-2 mb-1">
                {booking.rent_term?.schemas?.map(
                  (currentSchema, i) =>
                    i < 3 && (
                      <Badge
                        key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
                        className="flex-col items-start justify-start flex-grow h-full px-2 text-lg font-bold text-wrap"
                        variant="schema"
                      >
                        {`${formatRoubles(currentSchema.daily_amount!)}`}
                        <div className="text-xs font-medium text-black">{`${currentSchema.working_days}раб. /${currentSchema.non_working_days}вых.`}</div>
                      </Badge>
                    )
                )}
              </div>
              <p className="mb-2 text-base font-semibold text-gray">
                Минимум дней аренды: {booking.rent_term?.minimum_period_days}
              </p>
              <div className="min-h-28">
                {booking.car?.division?.park?.working_hours?.map((x) => (
                  <div className="flex items-center" key={x.day}>
                    <div className="text-sm capitalize w-28">{x.day}</div>{" "}
                    {x.start?.hours}:{x.start?.minutes} - {x.end?.hours}:
                    {x.end?.minutes}
                  </div>
                ))}
              </div>
            </>
          )}
          <Separator />
          <div className="flex items-center">
            <p className="w-1/2 font-semibold">Дата начала бронирования:</p>
            {booking.start_date}
          </div>
          <Separator />
          <div className="flex items-center">
            <p className="w-1/2 font-semibold">Дата окончания бронирования:</p>
            {booking.start_date}
          </div>
        </div>
      ))}
    </>
  );
};
