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

  const currentSchema = car.rent_term?.schemas![0]!;

  return (
    <div className="flex flex-col justify-center py-8 overflow-y-auto">
      <img
        className="object-cover w-full rounded-t-xl"
        src={car.images![0]}
        alt=""
      />
      <div className="space-y-2">
        <h1 className="my-4 text-center">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>

        <div className="flex flex-col justify-center h-32 space-y-2 ">
          {!phoneRequested && (
            <Button
              size={"lg"}
              onClick={() => setPhoneRequested(true)}
              variant="secondary"
            >
              Показать номер
            </Button>
          )}
          {phoneRequested && (
            <Button size={"lg"} variant="secondary">
              <a href={"tel:" + car.phone}>{car.phone}</a>
            </Button>
          )}
          <Button onClick={book} size={"lg"}>
            Забронировать
          </Button>
        </div>

        <p className="text-base font-semibold text-gray">
          Парк: {car.park_name}
        </p>
        <Separator />
        <p className="text-base font-semibold text-gray">
          Адрес: {car.division?.address}
        </p>
        <Separator />
        <p className="text-base font-semibold text-gray">
          Минимум дней аренды: {car.rent_term?.minimum_period_days}
        </p>
        <br />
        <div className="min-h-48">
          {car.working_hours?.map((x) => (
            <div className="flex items-center" key={x.day}>
              <div className="text-sm capitalize w-28">{x.day}</div> {x.start} -{" "}
              {x.end}
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

      <div className="flex flex-col items-center mx-auto space-y-2">
        <Badge
          className="mb-2 text-lg font-semibold"
          variant="outline"
        >{`${formatRoubles(currentSchema.daily_amount!)} ${
          currentSchema.working_days
        } раб./${currentSchema.non_working_days} вых`}</Badge>
        <Badge variant="outline">
          Депозит {formatRoubles(car.rent_term?.deposit_amount_total!)} (
          {formatRoubles(car.rent_term?.deposit_amount_daily!)}
          /день)
        </Badge>
        <Badge variant="outline">Комиссия {car.commission}</Badge>
        <div className="flex justify-around w-full">
          <Badge variant="outline">
            {getFuelTypeDisplayName(car.fuel_type)}
          </Badge>
          <Badge variant="outline">
            {getTransmissionDisplayName(car.transmission_type)}
          </Badge>
        </div>

        {!!car.self_employed && (
          <Badge variant="outline">Для самозанятых</Badge>
        )}
        {!!car.rent_term?.is_buyout_possible && (
          <Badge variant="outline">Выкуп автомобиля</Badge>
        )}
      </div>
    </div>
  );
};
