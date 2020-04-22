using System;
namespace ClientBL.Validators
{
    public enum ValidationResponse
    {
        OK,
        FieldCanotBeEmpty,
        FieldTooShort,
        AlreadyExists,
        InvalidFormation,
        UnidentifiedError

    }
}
