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
        "Загрузите селфи при хорошем освещении c главным разворотом страниц паспорта",
      type: DriverDocumentType.Image_fase_and_pasport,
      placeholderImg: selca,
    },
    {
      title: "Загрузите лицевую сторону водительского удостоверения",
      type: DriverDocumentType.Image_licence_front,
      placeholderImg: frontDriverId,
    },
    {
      title: "Загрузите обратную сторону водительского удостоверения",
      type: DriverDocumentType.Image_licence_back,
      placeholderImg: backDriverId,
    },
    {
      title: "Загрузите разворот вашего паспорта с фото",
      type: DriverDocumentType.Image_pasport_front,
      placeholderImg: frontPassport,
    },
    {
      title: "Загрузите страницу паспорта с разворотом прописки",
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
      <div className="mx-auto w-80">
        <h1 className="mt-8 text-center">Подтвердите свою личность</h1>

        {user.user_status === UserStatus.DocumentsNotUploaded && (
          <>
            <p className="p-4 text-xs font-bold text-center text-white rounded-lg bg-gradient-to-br from-amber-600 to-red">
              Вы не можете начать процесс бронирования пока не загрузили
              документы или документы не прошли верификацию
            </p>
            <h1 className="mt-4 text-3xl text-center text-red">
              {uploadedDocumentCount}/{requiredDocumentCount}
            </h1>
          </>
        )}

        {user.user_status === UserStatus.Verification && (
          <p className="p-4 text-xs font-bold text-center text-white rounded-lg bg-gradient-to-br from-sky-300 to-sky-800">
            Верификация в процессе
          </p>
        )}

        {user.user_status === UserStatus.Verified && (
          <p className="p-4 text-xs font-bold text-center text-white rounded-lg bg-gradient-to-br from-green-400 to-green-800">
            Вы прошли верификацию
          </p>
        )}

        {docs.map(({ title, type, placeholderImg }) => {
          const actualUrl =
            user.docs?.find((doc) => doc.type === type)?.url || placeholderImg;

          return (
            <div key={type} className="p-4 my-4 text-center rounded-lg shadow">
              <p className="">{title}</p>
              <img className="mx-auto my-8" src={actualUrl} />
              <div className="text-center">
                <FileInput
                  title="Загрузить"
                  onChange={(fileList) => onFileSelected(fileList[0], type)}
                />
              </div>
            </div>
          );
        })}
        <div className="my-8 text-center">
          <Button variant="reject" onClick={logout}>
            Выйти из приложения
          </Button>
        </div>
      </div>
    </>
  );
};
