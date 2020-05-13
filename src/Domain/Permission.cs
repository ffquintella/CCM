namespace Domain
{
    public class Permission
    {
        public long Id { get; set; }
        public PermissionType Type { get; set; }
        public PermissionConsent Consent { get; set; }
        public long EnvironmentId { get; set; }
        public long GroupId { get; set; }
        public long OwnerId { get; set; }
    }
}