import selca from "./assets/placeholders/selca.png";
import frontDriverId from "./assets/placeholders/front-driver-id.png";
import backDriverId from "./assets/placeholders/back-driver-id.png";
import frontPassport from "./assets/placeholders/front-passport.png";
import backPassport from "./assets/placeholders/back-passport.png";
import { Button } from "@/components/ui/button";

export const Account = () => (
  <>
    <div className="w-80 mx-auto">
      <p className="text-center text-red">
        Вы не можете начать процесс бронирование пока не загрузили документы или
        документы не прошли верификацию
      </p>
      <h1 className="text-center mt-8">Подтвердите свою личность</h1>
      <p className="text-center my-4">
        Загрузите селфи при хорошем освещении c главным разворотом страниц
        паспорта
      </p>
      <img className="my-8 mx-auto" src={selca} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      <h1 className="text-center mt-8">Регистрация документов</h1>
      <p className="text-center my-4">
        Загрузите лицевую сторону водительского удостоверения
      </p>
      <img className="my-8 mx-auto" src={frontDriverId} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите обратную сторону водительского удостоверения
      </p>
      <img className="my-8 mx-auto" src={backDriverId} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите разворот вашего паспорта с фото
      </p>
      <img className="my-8 mx-auto" src={frontPassport} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите страницу паспорта с разворотом прописки
      </p>
      <img className="my-8 mx-auto" src={backPassport} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      <div className="text-center my-8">
        <Button>Y</Button>
      </div>
      <div className="text-center my-8">
        <Button variant="reject">Отменить</Button>
      </div>
    </div>
  </>
);
