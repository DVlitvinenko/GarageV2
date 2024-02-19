import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { Cars2 } from "./api-client";
import { Separator } from "@/components/ui/separator";
import { useState } from "react";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { userAtom } from "./atoms";
import { useNavigate } from "react-router-dom";
import { useRecoilValue } from "recoil";

export const ModalCard = ({ car }: { car: Cars2 }) => {
  const [phoneRequested, setPhoneRequested] = useState(false);

  const user = useRecoilValue(userAtom);

  const navigate = useNavigate();

  const book = () => {
    if (!user) {
      navigate("login/driver");
    }
  };

  const currentSchemas = car.rent_term?.schemas;

  return (
    <>
      <div className="flex flex-col justify-center py-8 overflow-y-auto ">
        <img
          className="object-cover w-full rounded-t-xl"
          src={car.images![0]}
          alt=""
        />
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
                <div className="text-sm capitalize w-28">{x.day}</div> {x.start}{" "}
                - {x.end}
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

        <div className="flex flex-wrap items-center justify-center pb-10 space-x-1 space-y-1">
          <div className="flex flex-wrap items-center justify-center space-x-1 space-y-1">
            {currentSchemas?.map((currentSchema, i) => (
              <Badge
                key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
                className="mt-1 text-lg font-semibold"
                variant="card"
              >
                {`${formatRoubles(currentSchema.daily_amount!)} ${
                  currentSchema.working_days
                }/${currentSchema.non_working_days}`}
              </Badge>
            ))}
          </div>
          <Badge variant="card">
            Депозит {formatRoubles(car.rent_term?.deposit_amount_total!)}
            {/* (
            {formatRoubles(car.rent_term?.deposit_amount_daily!)}
            /день) */}
          </Badge>
          <Badge variant="card">Комиссия {car.commission}</Badge>
          <div className="flex justify-around">
            <Badge variant="card">
              {getFuelTypeDisplayName(car.fuel_type)}
            </Badge>
          </div>
          <div className="flex justify-around">
            <Badge variant="card">
              {getTransmissionDisplayName(car.transmission_type)}
            </Badge>
          </div>

          {!!car.self_employed && <Badge variant="card">Для самозанятых</Badge>}
          {!!car.rent_term?.is_buyout_possible && (
            <Badge variant="card">Выкуп автомобиля</Badge>
          )}
        </div>
      </div>
      <div className="fixed bottom-0 left-0 flex justify-center w-full py-4 space-x-2 bg-white border-t border-pale">
        {!phoneRequested && (
          <Badge
            variant="card"
            className="h-auto font-bold border-none bg-grey"
          >
            {`${formatRoubles(car.rent_term?.schemas![0]!.daily_amount)} ${
              car.rent_term?.schemas![0]!.working_days
            }/${car.rent_term?.schemas![0]!.non_working_days}`}
          </Badge>
        )}
        {phoneRequested && (
          <Button variant="secondary">
            <a href={"tel:" + car.phone}>{car.phone}</a>
          </Button>
        )}
        <Button onClick={book}>Забронировать</Button>
      </div>
    </>
  );
};
