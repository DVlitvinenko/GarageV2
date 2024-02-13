import * as React from "react";

const FileInput = (props: {
  title: string;
  onChange: (files: FileList) => void;
}) => (
  <>
    <label className="custom-file-upload">
      <input
        type="file"
        onChange={(e) => {
          if (e.target.files?.length) {
            props.onChange(e.target.files);
          }
        }}
      />
      {props.title}
    </label>
  </>
);

export default FileInput;
