import selca from "./assets/placeholders/selca.png";
import frontDriverId from "./assets/placeholders/front-driver-id.png";
import backDriverId from "./assets/placeholders/back-driver-id.png";
import frontPassport from "./assets/placeholders/front-passport.png";
import backPassport from "./assets/placeholders/back-passport.png";
import { Button } from "@/components/ui/button";
import FileInput from "@/components/ui/file-input";
import { useState } from "react";
import { client } from "./backend";
import {
  Docs, 
  DriverDocumentType,
  User,
} from "./api-client";
import {
  RecoilRoot,
  atom,
  selector,
  useSetRecoilState,
  useRecoilValue,
} from "recoil";
import { userAtom } from "./atoms";

export const Account = ({ user }: { user: User }) => {
  const [docs, setDocs] = useState<
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
    const { url } = await client.uploadFile(
      {
        fileName: "any",
        data: file,
      },
      documentType
    );

    const updatedDocs = user.docs!.map((x) => {
      const shallowCopy = new Docs({ ...x });

      if (x.type === documentType) {
        shallowCopy.url = url;
      }
      return shallowCopy;
    });

    setUser(new User({ ...user, docs: [...updatedDocs] }));
  };

  return (
    <>
      <div className="w-80 mx-auto">
        <h1 className="text-center mt-8">Подтвердите свою личность</h1>
        <p
          className="bg-gradient-to-br from-amber-600 to-red
                      rounded-lg p-4
                      text-center text-white font-bold text-xs"
        >
          Вы не можете начать процесс бронирования пока не загрузили документы
          или документы не прошли верификацию
        </p>
        <h1 className="text-center text-red text-3xl mt-4">
          {uploadedDocumentCount}/{requiredDocumentCount}
        </h1>

        {docs.map(({ title, type, placeholderImg }) => {
          const actualUrl =
            user.docs?.find((doc) => doc.type === type)?.url || placeholderImg;

          return (
            <div
              key={type}
              className="text-center my-4 p-4 shadow rounded-lg bg-slate-300"
            >
              <p className="">{title}</p>
              <img className="my-8 mx-auto" src={actualUrl} />
              <div className="text-center">
                <FileInput
                  title="Загрузить"
                  onChange={(fileList) => onFileSelected(fileList[0], type)}
                />
              </div>
            </div>
          );
        })}
        <div className="text-center my-8">
          <Button>Y</Button>
        </div>
        <div className="text-center my-8">
          <Button variant="reject">Отменить</Button>
        </div>
      </div>
    </>
  );
};
