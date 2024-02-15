import { useState } from "react";
import { client } from "./backend";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Body8, Body9 } from "./api-client";

export const DriverLogin = () => {
  const CODE_LENGTH = 8;

  const [codeRequested, setRequested] = useState(false);
  const [codeHasError, setCodeHasError] = useState(false);

  const [phone, setPhone] = useState("");
  const [code, setCode] = useState(0);

  const getCode = async () => {
    await client.createAndSendCode(new Body9({ phone }));
    setRequested(true);
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

  const handleCodeChange = (e: React.ChangeEvent<HTMLInputElement>) =>
    setCode(parseInt(e.target.value));

  return (
    <>
      <div className="mx-auto w-80">
        <h2 className="my-10 text-center">
          Зарегистрируйтесь, чтобы получить возможность бронирования автомобиля
          или войдите в личный кабинет.
        </h2>
        <Label>Введите ваш телефон</Label>
        <Input
          onChange={(e) => setPhone(e.target.value)}
          placeholder="+7 (999) 123-45-67"
        />

        {codeRequested && (
          <>
            <Label htmlFor="code">Введите полученную смс</Label>
            <Input
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

        <div className="text-center">
          {!codeRequested && (
            <Button onAsyncClick={getCode}>Получить код</Button>
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
            Нажимая 'Войти' вы соглашаетесь с{" "}
            <a className="text-blue-800 underline" href="kwol.ru">
              условиями договора
            </a>
          </div>
        )}
      </div>
    </>
  );
};
