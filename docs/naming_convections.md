# Domain Naming Convenctions
## Demand
Represents a request/query made to perform a specific action on an external service.

For example:
A user creates a **Demand** to execute `SELECT * FROM orders` on the External Service demandify_postgres.

## ExternalService
Registered and configured service within the application (in environment variables) that facilitates specific actions or queries.
Each of the External Service has his ExternalServiceConfiguration where eligible approvers to accept/deny demands are specified.

For example:
A user initiates a Demand to run a query `SELECT * FROM orders` on the **External Service** demandify_postgres.

## Notification
Represents an entity responsible for delivering messages related to significant events within the application. Notifications ensure that users are informed about the status and progress of their interactions with the system, including the lifecycle of Demands and Tasks.

For example:
- A **Notification** is sent to inform a User about the creation of a new Demand.
- Users receive a Notification when a Task is completed or when it fails to execute as expected.
- Notifications are used to inform Users about the approval or rejection of their Demands.

Notifications can be delivered via various channels such as Slack, or other linked UserSocialAccounts. 
## Task
Entity that tracks the execution and results of a Demand performed on an External Service. It contains all relevant details of the completed demand.

For example:
When a Demand to execute `SELECT * FROM orders` on demandify_postgres is approved and executed, the **Task** entity stores its results and metadata.

## User
Represents an individual utilizing the application to create, manage, and track Demands and Tasks.

## UserSocialAccount
External social account linked to a User. It may be used for authentication, notifications, or communication about Demands, approvals, and other activities. A User can have multiple linked Social Accounts.

For example:
A User Social Account such as Slack profile can notify the user of a new Demand approval.