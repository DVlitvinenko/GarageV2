import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { Body, Body16, Cars2, UserStatus } from "./api-client";
import { Separator } from "@/components/ui/separator";
import { useEffect, useState } from "react";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { userAtom } from "./atoms";
import { useNavigate } from "react-router-dom";
import { useRecoilValue } from "recoil";
import { client } from "./backend";

export const ModalCard = ({ car }: { car: Cars2 }) => {
  const [phoneRequested, setPhoneRequested] = useState(false);

  const user = useRecoilValue(userAtom);

  const navigate = useNavigate();

  const book = async () => {
    if (!user) {
      return navigate("login/driver");
    }
    if (user.user_status === UserStatus.Verified) {
      await client.booking(
        new Body16({
          id: car.id,
        })
      );
    } else {
      navigate("account");
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

        <div className="flex flex-wrap items-center justify-start gap-1 mb-3">
          <Badge variant="card" className="px-0 py-0 bg-grey ">
            <span className="flex items-center h-full px-2 bg-white rounded-xl">
              Депозит {formatRoubles(car.rent_term?.deposit_amount_total!)}
            </span>
            <span className="flex items-center h-full px-2 ">
              {formatRoubles(car.rent_term?.deposit_amount_daily!)}
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
          {currentSchemas?.map((currentSchema, i) => (
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
      <div className="fixed bottom-0 left-0 flex justify-center w-full px-4 py-4 space-x-2 bg-white border-t border-pale">
        {!phoneRequested && (
          <Badge variant="schema" className="w-1/2 h-auto border-none bg-grey">
            {`${formatRoubles(car.rent_term?.schemas![0]!.daily_amount)}`}
            <div className="text-xs font-medium text-black">{`${
              car.rent_term?.schemas![0]!.working_days
            }раб. /${car.rent_term?.schemas![0]!.non_working_days}вых.`}</div>
          </Badge>
        )}
        {phoneRequested && (
          <Button variant="secondary" className="w-1/2">
            <a href={"tel:" + car.phone}>{car.phone}</a>
          </Button>
        )}
        <Button onClick={book} className="w-1/2">
          Забронировать
        </Button>
      </div>
    </>
  );
};
