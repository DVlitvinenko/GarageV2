import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import {
  Body16,
  Body17,
  BookingStatus,
  Bookings,
  Cars2,
  User,
} from "./api-client";
import { Separator } from "@/components/ui/separator";
import {
  formatRoubles,
  formatWorkingTime,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { userAtom } from "./atoms";
import { useNavigate } from "react-router-dom";
import { useRecoilState } from "recoil";
import { client } from "./backend";
import Confirmation from "@/components/ui/confirmation";
import SliderImages from "@/components/ui/slider-images";

export const ModalCard = ({ car }: { car: Cars2 }) => {
  const [user, setUser] = useRecoilState(userAtom);

  const navigate = useNavigate();

  const activeBooking = user?.bookings!.find(
    (x) => x.status === BookingStatus.Booked
  );
  // временно удаляем проверку на верификацию!!!
  const book = async () => {
    if (!user) {
      return navigate("login/driver", {
        state: {
          bookingAttempt: true,
        },
      });
    }
    if (activeBooking) {
      await client.cancelBooking(
        new Body17({
          id: activeBooking!.id,
        })
      );
    }
    // if (user.user_status === UserStatus.Verified) {
    const bookingData = await client.book(
      new Body16({
        id: car.id,
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
          new Bookings(bookingData.booking),
        ],
      })
    );
    return navigate("bookings");
    // } else {
    //   navigate("account");
    // }
  };
  const { schemas } = car.rent_term!;

  return (
    <>
      <div className="flex flex-col justify-center pt-4 pb-8 overflow-y-auto ">
        <SliderImages images={car.images!} />
        {/* <div className="relative w-full h-52">
          <div className="flex space-x-4 overflow-scroll overflow-x-auto scrollbar-hide">
            {car.images!.map((x, i) => {
              return (
                <div
                  className={`min-w-[100%] h-52 pr-1 transition-transform ${
                    i === activeImageIndex ? "" : ""
                  }`}
                >
                  <img
                    className="object-cover w-full h-full rounded-xl"
                    src={x}
                    alt=""
                  />
                </div>
              );
            })}
          </div> */}
        {/* <div className="absolute bottom-0 flex px-1 py-1">
            {car.images!.map((x, i) => {
              return (
                <div
                  key={`modal_image_${i}`}
                  className={`w-full flex items-center bg-white rounded-xl transition-all h-14 ${
                    i === activeImageIndex
                      ? "shadow border-2 border-yellow"
                      : " scale-90"
                  }`}
                >
                  <img
                    className="object-cover w-full h-full rounded-xl"
                    src={x}
                    onClick={() => setActiveImageIndex(i)}
                    alt=""
                  />
                </div>
              );
            })}
          </div> */}
        {/* </div> */}
        <div className="space-y-2">
          <h1 className="my-4 text-center">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>

          <p className="text-base font-semibold text-gray">
            Парк: {car.park_name}
          </p>
          <Separator />
          <p className="text-base font-semibold text-gray">
            Адрес: {car.division?.address}
          </p>
          <Separator />
          <p className="text-base font-semibold text-gray">
            Телефон: {car.phone}
          </p>
          <Separator />
          <p className="text-base font-semibold text-gray">
            Минимум дней аренды: {car.rent_term?.minimum_period_days}
          </p>
          <br />
          <div className="min-h-48">
            {car.working_hours?.map((x) => (
              <div className="flex items-center" key={x.day}>
                <div className="text-sm capitalize w-28">{x.day}</div>
                {formatWorkingTime(
                  x.start!.hours!,
                  x.start!.minutes!,
                  x.end!.hours!,
                  x.end!.minutes!
                )}
              </div>
            ))}
          </div>
          <Collapsible>
            <CollapsibleTrigger>О парке ▼</CollapsibleTrigger>
            <CollapsibleContent>
              <div className="text-sm text-gray-700">{car.about}</div>
            </CollapsibleContent>
          </Collapsible>
        </div>

        <div className="flex flex-wrap items-center justify-start gap-1 mb-3">
          <Badge variant="card" className="px-0 py-0 bg-grey ">
            <span className="flex items-center h-full px-2 bg-white rounded-xl">
              Депозит {formatRoubles(car.rent_term!.deposit_amount_total!)}
            </span>
            <span className="flex items-center h-full px-2 ">
              {formatRoubles(car.rent_term!.deposit_amount_daily!)}
              /день
            </span>
          </Badge>
          <Badge variant="card">Комиссия {car.commission}</Badge>
          <Badge variant="card">{getFuelTypeDisplayName(car.fuel_type)}</Badge>
          <Badge variant="card">
            {getTransmissionDisplayName(car.transmission_type)}
          </Badge>

          {!!car.self_employed && <Badge variant="card">Для самозанятых</Badge>}
          {!!car.rent_term?.is_buyout_possible && (
            <Badge variant="card">Выкуп автомобиля</Badge>
          )}
        </div>
        <div className="flex flex-wrap gap-1 pb-20 mb-16">
          {schemas!.slice(0, 3).map((currentSchema, i) => (
            <Badge
              key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
              className="flex-col items-start justify-start flex-grow h-full px-2 text-lg font-bold text-wrap"
              variant="schema"
            >
              {`${formatRoubles(currentSchema.daily_amount!)}`}
              <div className="text-xs font-medium text-black">{`${currentSchema.working_days}раб. /${currentSchema.non_working_days}вых.`}</div>
            </Badge>
          ))}
        </div>
      </div>
      <div className="fixed bottom-0 left-0 flex justify-center w-full px-2 py-2 space-x-2 bg-white border-t border-pale">
        <Badge variant="schema" className="w-1/2 h-auto border-none bg-grey">
          {`${
            car.rent_term?.schemas![0]?.daily_amount !== undefined
              ? formatRoubles(car.rent_term?.schemas![0]!.daily_amount)
              : ""
          }`}
          <div className="text-xs font-medium text-black">{`${
            car.rent_term?.schemas![0]!.working_days
          }раб. /${car.rent_term?.schemas![0]!.non_working_days}вых.`}</div>
        </Badge>
        {!activeBooking && (
          <div className="w-1/2">
            <Confirmation
              title={`Забронировать ${car.brand} ${car.model}?`}
              type="green"
              accept={book}
              cancel={() => {}}
              trigger={<Button className="">Забронировать</Button>}
            />
          </div>
        )}
        {!!activeBooking && (
          <div className="w-1/2">
            <Confirmation
              title={`У вас есть активная бронь: ${activeBooking.car?.brand} ${activeBooking.car?.model}. Отменить и забронировать ${car.brand} ${car.model}?`}
              type="green"
              accept={book}
              cancel={() => {}}
              trigger={<Button className="">Забронировать</Button>}
            />
          </div>
        )}
      </div>
    </>
  );
};
