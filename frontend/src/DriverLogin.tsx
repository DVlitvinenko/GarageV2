import { useState } from "react";
import { client } from "./backend";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Body8, Body9 } from "./api-client";
import React, { ChangeEvent } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useTimer } from "react-timer-hook";
export const DriverLogin = () => {
  const CODE_LENGTH = 8;

  const [codeRequested, setRequested] = useState(false);
  const [codeHasError, setCodeHasError] = useState(false);
  const [codeAttempts, setCodeAttempts] = useState(0);

  const [phone, setPhone] = useState("");
  const [code, setCode] = useState(0);

  const location = useLocation();

  const getCode = async () => {
    const time = new Date();

    codeAttempts < 3
      ? time.setSeconds(time.getSeconds() + 60)
      : time.setSeconds(time.getSeconds() + 300);
    codeAttempts < 3 ? setCodeAttempts(codeAttempts + 1) : setCodeAttempts(0);
    await client.createAndSendCode(new Body9({ phone }));
    setRequested(true);
    restart(time);
  };

  const login = async () => {
    try {
      const access_token = await client.loginOrRegister(
        new Body8({ phone, code })
      );

      localStorage.setItem("token", access_token!);

      window.location.href = "/";
    } catch (error) {
      setCodeHasError(true);
    }
  };

  const { minutes, seconds, restart } = useTimer({
    expiryTimestamp: new Date(),
    autoStart: false,
  });

  const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) =>
    setCode(parseInt(e.target.value));

  const handlePhoneChange = (e: ChangeEvent<HTMLInputElement>) => {
    const input = e.target.value.replace(/\D/g, "");
    let formattedPhone = "+";

    if (input.length > 0) {
      if (input[0] === "7") {
        formattedPhone += "7";
      } else {
        formattedPhone += "7";
      }
    }

    if (input.length > 1) {
      formattedPhone += ` (${input.substring(1, 4)}`;
    }

    if (input.length > 4) {
      formattedPhone += `) ${input.substring(4, 7)}`;
    }

    if (input.length > 7) {
      formattedPhone += `-${input.substring(7, 9)}`;
    }

    if (input.length > 9) {
      formattedPhone += `-${input.substring(9, 11)}`;
    }

    setPhone(formattedPhone);
  };

  return (
    <>
      <div className="mx-auto w-80">
        <h2 className="my-10 text-center">
          Зарегистрируйтесь или войдите в личный кабинет.
        </h2>
        {location?.state?.bookingAttempt && (
          <div className="mb-8 text-center text-red ">
            Чтобы забронировать машину - зарегистрируйтесь
          </div>
        )}
        <Label>Введите ваш телефон</Label>
        <Input
          className="mt-1"
          onChange={handlePhoneChange}
          value={phone}
          type="text"
          placeholder="+7 (999) 123-45-67"
        />

        {codeRequested && (
          <>
            <Label htmlFor="code">Введите код из смс</Label>
            <Input
              className="mt-1"
              onChange={handleCodeChange}
              id="code"
              placeholder="_ _ _ _ _ _ _"
            />
            {codeHasError && (
              <p className="my-4 text-center text-red">
                Вы ввели неправильный код
              </p>
            )}
          </>
        )}

        <div className="space-y-6 text-center">
          {!codeRequested && (
            <Button onAsyncClick={getCode}>Получить код</Button>
          )}
          {codeRequested && !(!!minutes || !!seconds) && (
            <Button variant={"reject"} onAsyncClick={getCode}>
              Отправить код повторно
            </Button>
          )}
          {(!!minutes || !!seconds) && (
            <Button className="bg-grey active:bg-grey">
              Повторная отправка кода через: ({`${minutes}:${seconds}`})
            </Button>
          )}
          {codeRequested && (
            <Button
              onAsyncClick={login}
              disabled={code.toString().length != CODE_LENGTH}
            >
              Войти
            </Button>
          )}
        </div>

        {codeRequested && (
          <div className="my-4 text-center">
            Нажимая &laquo;Войти&raquo; вы соглашаетесь с{" "}
            <a className="text-blue-800 underline" href="kwol.ru">
              условиями договора
            </a>
          </div>
        )}
      </div>
    </>
  );
};
