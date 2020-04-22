namespace Domain.Authentication
{
    public enum AuthenticationStatus
    {
        NotLoggedIn,
        TokenExpired,
        AccountLocked,
        OK
    }
}