import { useEffect, useRef, useState } from "react";
import { client } from "./backend";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Body8, Body9 } from "./api-client";
import React, { ChangeEvent } from "react";
import { useLocation } from "react-router-dom";
import { useTimer } from "react-timer-hook";
export const DriverLogin = () => {
  const CODE_LENGTH = 4;

  const [codeRequested, setRequested] = useState(false);
  const [codeHasError, setCodeHasError] = useState(false);
  const [codeAttempts, setCodeAttempts] = useState(0);

  const [phone, setPhone] = useState("");
  const [code, setCode] = useState(0);

  const inputRef = useRef(null);

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
    setTimeout(() => {
      inputRef.current && inputRef.current.focus();
    }, 100);
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

  useEffect(() => {
    const storedTimerState = localStorage.getItem("timerState");
    if (storedTimerState) {
      const timerState = JSON.parse(storedTimerState);
      if (timerState) {
        const expiryTimestamp = new Date(
          new Date().getTime() +
            timerState.minutes * 60 * 1000 +
            timerState.seconds * 1000
        );
        setRequested(true);
        restart(expiryTimestamp);
      }
    }
  }, []);

  useEffect(() => {
    localStorage.setItem("timerState", JSON.stringify({ minutes, seconds }));
  }, [minutes, seconds]);

  const handleFocus = () => {
    if (inputRef.current) {
      setTimeout(() => {
        inputRef.current.setSelectionRange(0, 0);
        inputRef.current.click();
      }, 300);
    }
  };

  useEffect(() => {
    if (!codeRequested) {
      localStorage.removeItem("timerState");
    }
  }, [codeRequested]);

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
      <div className="mx-auto w-80 sm:w-full">
        <h2 className="my-10 text-center">
          Зарегистрируйтесь или войдите в личный кабинет.
        </h2>
        {location?.state?.bookingAttempt && (
          <div className="mb-8 text-center text-red ">
            Чтобы забронировать машину - зарегистрируйтесь
          </div>
        )}
        <div className="max-w-sm mx-auto">
          <Label>Введите ваш телефон</Label>
          <Input
            type="tel"
            pattern="[0-9]{1} [0-9]{3} [0-9]{3}-[0-9]{2}-[0-9]{2}"
            className="mt-1"
            onChange={handlePhoneChange}
            value={phone}
            placeholder="+7 (999) 123-45-67"
            autoComplete="tel-national"
          />

          {codeRequested && (
            <>
              <Label htmlFor="code">Введите код из смс</Label>
              <Input
                type="number"
                inputMode="numeric"
                ref={inputRef}
                className="mt-1"
                onChange={handleCodeChange}
                id="code"
                placeholder="_ _ _ _"
                onFocus={handleFocus}
                autoFocus={true}
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
              <Button className="bg-grey active:bg-grey hover:bg-grey">
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
      </div>
    </>
  );
};
