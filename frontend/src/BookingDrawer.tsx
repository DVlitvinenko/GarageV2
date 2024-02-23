import { Button } from "@/components/ui/button";
import { Drawer, DrawerContent, DrawerFooter } from "@/components/ui/drawer";
import { Bookings } from "./api-client";
import { Separator } from "@/components/ui/separator";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger
} from "@/components/ui/collapsible";

export const BookingDrawer = ({
  open, booking,
}: {
  open: boolean;
  booking: Bookings;
}) => {
  if (!booking) {return<></>}  
  return (
      <>
           
            <div className="py-8 overflow-y-auto h-[%]">
              <img
                className="object-cover w-full rounded-t-xl min-h-24"
                src={booking.car!.images![0]}
                alt="" />
              <div className="space-y-2">
                <h1 className="my-4 text-center">{`${booking.car!.brand} ${booking.car!.model} ${booking.car!.year_produced}`}</h1>

                <p className="text-base font-semibold text-gray">
                  Парк: {booking.car!.division?.park?.park_name}
                </p>
                <Separator />
                <p className="text-base font-semibold text-gray">
                  Адрес: {booking.car!.division?.address}
                </p>
                <Separator />
                <p className="text-base font-semibold text-gray">
                  Телефон: {booking.car!.division?.park?.phone}
                </p>
                <Separator />
                <p className="text-base font-semibold text-gray">
                  Минимум дней аренды: {booking.car!.rent_term?.minimum_period_days}
                </p>
                <br />
                <div className="min-h-48">
                  {booking.car!.working_hours?.map((x) => (
                    <div className="flex items-center" key={x.day}>
                      <div className="text-sm capitalize w-28">{x.day}</div>{" "}
                      {x.start} - {x.end}
                    </div>
                  ))}
                </div>
                <Collapsible>
                  <CollapsibleTrigger>О парке ▼</CollapsibleTrigger>
                  <CollapsibleContent>
                    <div className="text-sm text-gray-700">
                      {booking.car!.division?.park?.about}
                    </div>
                  </CollapsibleContent>
                </Collapsible>
              </div>

              {/* <div className="flex flex-wrap items-center justify-start gap-1 mb-3">
            <Badge variant="card" className="px-0 py-0 bg-grey ">
              <span className="flex items-center h-full px-2 bg-white rounded-xl">
                Депозит{" "}
                {formatRoubles(booking.rent_term!.deposit_amount_total!)}
              </span>
              <span className="flex items-center h-full px-2 ">
                {formatRoubles(booking.rent_term?.deposit_amount_daily!)}
                /день
              </span>
            </Badge>
            <Badge variant="card">
              Комиссия {booking.car!.division?.park?.commission}%
            </Badge>
            <Badge variant="card">
              {getFuelTypeDisplayName(booking.car!.fuel_type)}
            </Badge>
            <Badge variant="card">
              {getTransmissionDisplayName(booking.car!.transmission_type)}
            </Badge>
  
            {!!booking.car!.division?.park?.self_employed && (
              <Badge variant="card">Для самозанятых</Badge>
            )}
            {!!booking.rent_term?.is_buyout_possible && (
              <Badge variant="card">Выкуп автомобиля</Badge>
            )}
          </div> */}
              {/* <div className="flex flex-wrap gap-1 pb-20 mb-16">
            {booking.rent_term!.schemas!.map((currentSchema, i) => (
              <Badge
                key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
                className="flex-col items-start justify-start flex-grow h-full px-2 text-lg font-bold text-wrap"
                variant="schema"
              >
                {`${formatRoubles(currentSchema.daily_amount!)}`}
                <div className="text-xs font-medium text-black">{`${currentSchema.working_days}раб. /${currentSchema.non_working_days}вых.`}</div>
              </Badge>
            ))}
          </div> */}
            </div>
            {/* <div className="fixed bottom-0 left-0 flex justify-center w-full px-4 py-4 space-x-2 bg-white border-t border-pale">
            
            </div> */}

            <DrawerFooter>
              <Button variant="reject">Отменить бронь </Button>
              {/* <DrawerClose>
            <Button variant="outline"></Button>
          </DrawerClose> */}
            </DrawerFooter>
          </DrawerContent>
        </Drawer>
      </>
    );
  };
