import selca from "./assets/placeholders/selca.png";
import frontDriverId from "./assets/placeholders/front-driver-id.png";
import backDriverId from "./assets/placeholders/back-driver-id.png";
import frontPassport from "./assets/placeholders/front-passport.png";
import backPassport from "./assets/placeholders/back-passport.png";
import { Button } from "@/components/ui/button";
import FileInput from "@/components/ui/file-input";
import { useState } from "react";
import { client } from "./backend";
import { DriverDocumentType, User, UserStatus } from "./api-client";
import { useSetRecoilState } from "recoil";
import { userAtom } from "./atoms";

export const Account = ({ user }: { user: User }) => {
  // lol wtf ejection
  if (!user) {
    return <></>;
  }

  const [docs] = useState<
    {
      type: DriverDocumentType;
      url?: string;
      title: string;
      placeholderImg: string;
    }[]
  >([
    {
      title:
        "Селфи при хорошем освещении c главным разворотом страниц паспорта",
      type: DriverDocumentType.Image_fase_and_pasport,
      placeholderImg: selca,
    },
    {
      title: "Лицевая сторона водительского удостоверения",
      type: DriverDocumentType.Image_licence_front,
      placeholderImg: frontDriverId,
    },
    {
      title: "Обратная сторона водительского удостоверения",
      type: DriverDocumentType.Image_licence_back,
      placeholderImg: backDriverId,
    },
    {
      title: "Разворот страниц паспорта с фото",
      type: DriverDocumentType.Image_pasport_front,
      placeholderImg: frontPassport,
    },
    {
      title: "Разворот страниц паспорта с пропиской",
      type: DriverDocumentType.Image_pasport_address,
      placeholderImg: backPassport,
    },
  ]);

  const setUser = useSetRecoilState(userAtom);

  const requiredDocumentCount = docs.length;
  const uploadedDocumentCount = user.docs?.filter((x) => !!x.url).length || 0;

  const onFileSelected = async (
    file: File,
    documentType: DriverDocumentType
  ) => {
    await client.uploadFile(
      {
        fileName: "any",
        data: file,
      },
      documentType
    );

    const userData = await client.getUser();
    setUser(userData.user!);

    // const updatedDocs = user.docs!.map((x) => {
    //   const shallowCopy = new Docs({ ...x });

    //   if (x.type === documentType) {
    //     shallowCopy.url = url;
    //   }
    //   return shallowCopy;
    // });

    // setUser(new User({ ...user, docs: [...updatedDocs] }));
  };

  const logout = async () => {
    try {
      await client.logout();
    } catch (error) {}

    localStorage.clear();
    window.location.href = "/";
  };

  return (
    <>
      <div className="mx-auto w-80 sm:w-full sm:mx-0">
        <h1 className="mt-8 text-center md:text-xl">
          Подтвердите свою личность
        </h1>

        {user.user_status === UserStatus.DocumentsNotUploaded && (
          <>
            <p className="max-w-sm p-4 mx-auto text-xs font-bold text-center text-white rounded-lg md:max-w-lg md:text-xl bg-gradient-to-br from-amber-600 to-red">
              Вы не можете начать процесс бронирования пока не загрузили
              документы или документы не прошли верификацию
            </p>
            <h1 className="mt-4 text-3xl text-center text-red">
              {uploadedDocumentCount}/{requiredDocumentCount}
            </h1>
          </>
        )}

        {user.user_status === UserStatus.Verification && (
          <p className="p-4 text-xs font-bold text-center text-white rounded-lg bg-gradient-to-br from-sky-300 to-sky-800 md:max-w-lg">
            Верификация в процессе
          </p>
        )}

        {user.user_status === UserStatus.Verified && (
          <p className="p-4 text-xs font-bold text-center text-white rounded-lg bg-gradient-to-br from-green-400 to-green-800 md:max-w-lg">
            Вы прошли верификацию
          </p>
        )}

        <div className="flex flex-wrap justify-center gap-2">
          {docs.map(({ title, type, placeholderImg }) => {
            const actualUrl =
              user.docs?.find((doc) => doc.type === type)?.url ||
              placeholderImg;

            return (
              <div
                key={type}
                className="p-4 my-4 text-center rounded-lg shadow max-w-[320px] md:max-w-[540px] md:flex md:flex-col md:justify-between"
              >
                <p className="md:text-xl">{title}</p>
                <img
                  className="mx-auto my-8 md:max-h-96"
                  src={actualUrl}
                  alt=""
                />
                <div className="text-center md:text-xl">
                  <FileInput
                    title="Загрузить"
                    onChange={(fileList) => onFileSelected(fileList[0], type)}
                  />
                </div>
              </div>
            );
          })}
        </div>
        <div className="my-8 text-center max-w-[320px] mx-auto">
          <Button variant="reject" className="md:text-xl" onClick={logout}>
            Выйти из приложения
          </Button>
        </div>
      </div>
    </>
  );
};
