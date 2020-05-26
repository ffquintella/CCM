using System;

namespace CCM_API.Exceptions
{
    /// <summary>
    /// Used to sinalize an operation tried by a user without a permission
    /// </summary>
    public class NoPermissionException: Exception
    {
        public long UserId { get; set; }
        public NoPermissionException(long userId)
        {
            UserId = userId;
                //= string.Format("User with id={0} has no permission for operation", userId);
        }
    }
}